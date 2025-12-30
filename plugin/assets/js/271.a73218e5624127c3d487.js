"use strict";
(self["webpackChunkPDFBuilder"] = self["webpackChunkPDFBuilder"] || []).push([[271],{

/***/ 271:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  PDFBuilder: () => (/* binding */ PDFBuilder),
  PDFBuilderContent: () => (/* reexport */ PDFBuilderContent)
});

// EXTERNAL MODULE: ./node_modules/react/jsx-runtime.js
var jsx_runtime = __webpack_require__(848);
// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
;// ./assets/js/pdf-builder-react/utils/debug.ts
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
        const debugSettings = window.pdfBuilderCanvasSettings.debug;
        if (debugSettings && typeof debugSettings === 'object') {
            return !!debugSettings.javascript;
        }
    }
    return false;
}
// Fonction de logging conditionnel
function debugLog(...args) {
    if (isDebugEnabled()) {
        console.log(...args);
    }
}
// Fonction de debug pour les sauvegardes (activable séparément)
function debugSave(...args) {
    if (isDebugEnabled()) {
        console.log(...args);
    }
}
function debug_debugError(...args) {
    if (isDebugEnabled()) {
        console.error(...args);
    }
}
function debugWarn(...args) {
    if (isDebugEnabled()) {
        console.warn(...args);
    }
}
function debugTable(data) {
    if (isDebugEnabled()) {
        console.table(data);
    }
}
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
        window.addEventListener('pdfBuilder:debugSettingsChanged', (e) => {
            try {
                const detail = e && e.detail ? e.detail : window.pdfBuilderDebugSettings;
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

;// ./assets/js/pdf-builder-react/contexts/CanvasSettingsContext.tsx



const CanvasSettingsContext = (0,react.createContext)(undefined);
// Valeurs par défaut
const DEFAULT_SETTINGS = {
    canvasWidth: 210,
    canvasHeight: 297,
    canvasUnit: 'mm',
    canvasOrientation: 'portrait',
    canvasBackgroundColor: '#ffffff',
    containerBackgroundColor: '#f8f9fa',
    borderColor: '#cccccc',
    borderWidth: 1,
    shadowEnabled: false,
    marginTop: 20,
    marginRight: 20,
    marginBottom: 20,
    marginLeft: 20,
    showMargins: false,
    gridShow: true,
    gridSize: 10,
    gridColor: '#e5e7eb',
    gridSnapEnabled: true,
    gridSnapTolerance: 8,
    guidesEnabled: true,
    // 🔍 Zoom & Navigation
    navigationEnabled: true,
    zoomDefault: 100,
    zoomMin: 10,
    zoomMax: 500,
    zoomStep: 25,
    selectionDragEnabled: true,
    selectionMultiSelectEnabled: true,
    selectionRotationEnabled: false,
    selectionCopyPasteEnabled: true,
    selectionShowHandles: true,
    selectionHandleSize: 8,
    selectionHandleColor: '#0066cc',
    canvasSelectionMode: 'click',
    exportQuality: 100,
    exportFormat: 'pdf',
    exportCompression: true,
    exportIncludeMetadata: true,
    historyUndoLevels: 50,
    historyRedoLevels: 50,
    // Performance & Lazy Loading
    lazyLoadingEditor: true,
    lazyLoadingPlugin: true,
    debugMode: false,
    memoryLimitJs: 256,
    isLoading: true,
    isReady: false,
    error: null,
    refreshSettings: () => { }
};
function loadSettingsFromWindowObj() {
    var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l, _m, _o, _p, _q, _r, _s, _t, _u, _v, _w, _x, _y, _z, _0, _1;
    try {
        const windowSettings = ((_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.canvasSettings) || window.pdfBuilderCanvasSettings;
        // Debug: Check if window settings exist - FORCE LOG
        if (typeof window !== 'undefined' && window.pdfBuilderCanvasSettings) {
        }
        else {
        }
        if (!windowSettings) {
            return {
                ...DEFAULT_SETTINGS,
                isLoading: false,
                isReady: true,
                updateGridSettings: () => { },
                saveGridSettings: async () => { }
            };
        }
        // Mapper les paramètres depuis le format WordPress vers notre format
        const newSettings = {
            // Dimensions
            canvasWidth: (_b = windowSettings.canvas_width) !== null && _b !== void 0 ? _b : DEFAULT_SETTINGS.canvasWidth,
            canvasHeight: (_c = windowSettings.canvas_height) !== null && _c !== void 0 ? _c : DEFAULT_SETTINGS.canvasHeight,
            canvasUnit: (_d = windowSettings.canvas_unit) !== null && _d !== void 0 ? _d : DEFAULT_SETTINGS.canvasUnit,
            canvasOrientation: (_e = windowSettings.canvas_orientation) !== null && _e !== void 0 ? _e : DEFAULT_SETTINGS.canvasOrientation,
            // Couleurs
            canvasBackgroundColor: (_f = windowSettings.canvas_background_color) !== null && _f !== void 0 ? _f : DEFAULT_SETTINGS.canvasBackgroundColor,
            containerBackgroundColor: (_g = windowSettings.container_background_color) !== null && _g !== void 0 ? _g : DEFAULT_SETTINGS.containerBackgroundColor,
            borderColor: (_h = windowSettings.border_color) !== null && _h !== void 0 ? _h : DEFAULT_SETTINGS.borderColor,
            borderWidth: (_j = windowSettings.border_width) !== null && _j !== void 0 ? _j : DEFAULT_SETTINGS.borderWidth,
            shadowEnabled: windowSettings.shadow_enabled === true || windowSettings.shadow_enabled === '1',
            // Marges
            marginTop: (_k = windowSettings.margin_top) !== null && _k !== void 0 ? _k : DEFAULT_SETTINGS.marginTop,
            marginRight: (_l = windowSettings.margin_right) !== null && _l !== void 0 ? _l : DEFAULT_SETTINGS.marginRight,
            marginBottom: (_m = windowSettings.margin_bottom) !== null && _m !== void 0 ? _m : DEFAULT_SETTINGS.marginBottom,
            marginLeft: (_o = windowSettings.margin_left) !== null && _o !== void 0 ? _o : DEFAULT_SETTINGS.marginLeft,
            showMargins: windowSettings.show_margins === true || windowSettings.show_margins === '1',
            // Grille
            gridShow: windowSettings.show_grid === true || windowSettings.show_grid === '1',
            gridSize: (windowSettings.show_grid === true || windowSettings.show_grid === '1')
                ? ((_p = windowSettings.grid_size) !== null && _p !== void 0 ? _p : DEFAULT_SETTINGS.gridSize)
                : 0,
            gridColor: (_q = windowSettings.grid_color) !== null && _q !== void 0 ? _q : DEFAULT_SETTINGS.gridColor,
            gridSnapEnabled: (windowSettings.show_grid === true || windowSettings.show_grid === '1') && (windowSettings.snap_to_grid === true || windowSettings.snap_to_grid === '1'),
            gridSnapTolerance: (_r = windowSettings.snap_tolerance) !== null && _r !== void 0 ? _r : DEFAULT_SETTINGS.gridSnapTolerance,
            guidesEnabled: windowSettings.show_guides === true || windowSettings.show_guides === '1',
            // 🔍 Zoom & Navigation
            navigationEnabled: windowSettings.navigation_enabled === true || windowSettings.navigation_enabled === '1',
            zoomDefault: (() => {
                var _a, _b, _c;
                const minZoom = Math.max(1, (_a = windowSettings.min_zoom) !== null && _a !== void 0 ? _a : DEFAULT_SETTINGS.zoomMin);
                const maxZoom = Math.max(minZoom, (_b = windowSettings.max_zoom) !== null && _b !== void 0 ? _b : DEFAULT_SETTINGS.zoomMax);
                const defaultZoom = (_c = windowSettings.default_zoom) !== null && _c !== void 0 ? _c : DEFAULT_SETTINGS.zoomDefault;
                return Math.max(minZoom, Math.min(maxZoom, defaultZoom));
            })(),
            zoomMin: Math.max(1, (_s = windowSettings.min_zoom) !== null && _s !== void 0 ? _s : DEFAULT_SETTINGS.zoomMin),
            zoomMax: (() => {
                var _a, _b;
                const minZoom = Math.max(1, (_a = windowSettings.min_zoom) !== null && _a !== void 0 ? _a : DEFAULT_SETTINGS.zoomMin);
                const maxZoom = (_b = windowSettings.max_zoom) !== null && _b !== void 0 ? _b : DEFAULT_SETTINGS.zoomMax;
                return Math.max(minZoom, maxZoom);
            })(),
            zoomStep: Math.max(1, (_t = windowSettings.zoom_step) !== null && _t !== void 0 ? _t : DEFAULT_SETTINGS.zoomStep),
            // Sélection
            selectionDragEnabled: windowSettings.drag_enabled === true || windowSettings.drag_enabled === '1',
            selectionMultiSelectEnabled: windowSettings.multi_select === true || windowSettings.multi_select === '1',
            selectionRotationEnabled: windowSettings.enable_rotation === true || windowSettings.enable_rotation === '1',
            selectionCopyPasteEnabled: windowSettings.copy_paste_enabled === true || windowSettings.copy_paste_enabled === '1',
            selectionShowHandles: windowSettings.show_resize_handles === true || windowSettings.show_resize_handles === '1',
            selectionHandleSize: (_u = windowSettings.handle_size) !== null && _u !== void 0 ? _u : DEFAULT_SETTINGS.selectionHandleSize,
            selectionHandleColor: (_v = windowSettings.handle_color) !== null && _v !== void 0 ? _v : DEFAULT_SETTINGS.selectionHandleColor,
            canvasSelectionMode: (_w = windowSettings.canvas_selection_mode) !== null && _w !== void 0 ? _w : DEFAULT_SETTINGS.canvasSelectionMode,
            // Export
            exportQuality: (_x = windowSettings.image_quality) !== null && _x !== void 0 ? _x : DEFAULT_SETTINGS.exportQuality,
            exportFormat: (_y = windowSettings.export_format) !== null && _y !== void 0 ? _y : DEFAULT_SETTINGS.exportFormat,
            exportCompression: windowSettings.compress_images === true || windowSettings.compress_images === '1',
            exportIncludeMetadata: windowSettings.include_metadata === true || windowSettings.include_metadata === '1',
            // Historique
            historyUndoLevels: (_z = windowSettings.history_undo_levels) !== null && _z !== void 0 ? _z : DEFAULT_SETTINGS.historyUndoLevels,
            historyRedoLevels: (_0 = windowSettings.history_redo_levels) !== null && _0 !== void 0 ? _0 : DEFAULT_SETTINGS.historyRedoLevels,
            // Performance & Lazy Loading
            lazyLoadingEditor: windowSettings.lazy_loading_editor === true || windowSettings.lazy_loading_editor === '1',
            lazyLoadingPlugin: windowSettings.lazy_loading_plugin === true || windowSettings.lazy_loading_plugin === '1',
            debugMode: windowSettings.debug_mode === true || windowSettings.debug_mode === '1',
            memoryLimitJs: (_1 = windowSettings.memory_limit_js) !== null && _1 !== void 0 ? _1 : DEFAULT_SETTINGS.memoryLimitJs,
            isLoading: false,
            isReady: true,
            error: null,
            refreshSettings: () => { },
            updateGridSettings: () => { },
            saveGridSettings: async () => { }
        };
        return newSettings;
    }
    catch (_err) {
        const errorMsg = _err instanceof Error ? _err.message : 'Unknown error';
        return {
            ...DEFAULT_SETTINGS,
            isLoading: false,
            isReady: false,
            error: errorMsg,
            refreshSettings: () => { },
            updateGridSettings: () => { },
            saveGridSettings: async () => { }
        };
    }
}
function CanvasSettingsProvider({ children }) {
    const [settings, setSettings] = (0,react.useState)(() => {
        // Try to load from window object first
        const windowSettings = loadSettingsFromWindowObj();
        return windowSettings;
    });
    // ✅ CORRECTION: Flag pour éviter les boucles infinies lors des mises à jour d'événements
    const isUpdatingFromEventRef = (0,react.useRef)(false);
    const hasInitializedRef = (0,react.useRef)(false);
    // Function to refresh settings from window object
    const handleRefresh = () => {
        const windowSettings = loadSettingsFromWindowObj();
        setSettings(windowSettings);
    };
    // Load settings from server on mount - simplified - ONLY ONCE
    (0,react.useEffect)(() => {
        if (hasInitializedRef.current)
            return; // Éviter les doublons au montage
        hasInitializedRef.current = true;
        // For now, just use window settings - AJAX calls can be added later if needed
        const windowSettings = loadSettingsFromWindowObj();
        setSettings(windowSettings);
    }, []);
    // Listen for settings update events
    (0,react.useEffect)(() => {
        const handleSettingsUpdate = () => {
            if (isUpdatingFromEventRef.current)
                return; // Éviter les boucles infinies
            isUpdatingFromEventRef.current = true;
            const windowSettings = loadSettingsFromWindowObj();
            setSettings(windowSettings);
            // Reset flag after a short delay
            setTimeout(() => {
                isUpdatingFromEventRef.current = false;
            }, 100);
        };
        window.addEventListener('pdfBuilderCanvasSettingsUpdated', handleSettingsUpdate, { passive: true });
        return () => window.removeEventListener('pdfBuilderCanvasSettingsUpdated', handleSettingsUpdate);
    }, []);
    // Synchronisation automatique : si gridShow est désactivé, désactiver gridSnapEnabled et gridSize
    // Commenté pour éviter les boucles infinies de rendu
    // useEffect(() => {
    //   if (!settings.gridShow) {
    //     let needsUpdate = false;
    //     const updates: Partial<CanvasSettingsContextType> = {};
    //     if (settings.gridSnapEnabled) {
    //       updates.gridSnapEnabled = false;
    //       needsUpdate = true;
    //     }
    //     if (settings.gridSize !== 0) {
    //       updates.gridSize = 0;
    //       needsUpdate = true;
    //     }
    //     if (needsUpdate) {
    //       setSettings(prev => ({
    //         ...prev,
    //         ...updates
    //       }));
    //     }
    //   }
    // }, [settings.gridShow, settings.gridSnapEnabled, settings.gridSize]);
    // Ajouter la fonction refreshSettings au contexte final
    const contextValue = {
        ...settings,
        updateGridSettings: (newSettings) => {
            setSettings(prev => ({ ...prev, ...newSettings }));
        },
        saveGridSettings: async (newSettings) => {
            var _a, _b;
            try {
                // Appliquer la synchronisation automatique
                const syncedSettings = { ...newSettings };
                if (newSettings.gridShow === false) {
                    syncedSettings.gridSize = 0;
                    syncedSettings.gridSnapEnabled = false;
                }
                // Préparer les données pour l'AJAX
                const formData = new URLSearchParams();
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('nonce', ((_a = window.pdfBuilderAjax) === null || _a === void 0 ? void 0 : _a.nonce) || '');
                if (syncedSettings.gridShow !== undefined) {
                    formData.append('canvas_grid_enabled', syncedSettings.gridShow ? '1' : '0');
                }
                if (syncedSettings.gridSize !== undefined) {
                    formData.append('canvas_grid_size', syncedSettings.gridSize.toString());
                }
                if (syncedSettings.gridSnapEnabled !== undefined) {
                    formData.append('canvas_snap_to_grid', syncedSettings.gridSnapEnabled ? '1' : '0');
                }
                // Sauvegarder côté serveur
                const response = await fetch(((_b = window.pdfBuilderAjax) === null || _b === void 0 ? void 0 : _b.ajax_url) || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        // Mettre à jour l'état local
                        setSettings(prev => ({ ...prev, ...syncedSettings }));
                    }
                    else {
                        debug_debugError('Erreur lors de la sauvegarde des paramètres de grille:', result.message);
                    }
                }
                else {
                    debug_debugError('Erreur HTTP lors de la sauvegarde des paramètres de grille');
                }
            }
            catch (error) {
                debug_debugError('Erreur lors de la sauvegarde des paramètres de grille:', error);
            }
        },
        refreshSettings: handleRefresh
    };
    return ((0,jsx_runtime.jsx)(CanvasSettingsContext.Provider, { value: contextValue, children: children }));
}
function CanvasSettingsContext_useCanvasSettings() {
    const context = (0,react.useContext)(CanvasSettingsContext);
    if (context === undefined) {
        throw new Error('useCanvasSettings must be used within CanvasSettingsProvider');
    }
    return context;
}

;// ./assets/js/pdf-builder-react/contexts/builder/BuilderContext.tsx




// Fonction helper pour corriger les positions des éléments hors limites
// ✅ BUGFIX-014: Accept canvas dimensions as parameters for dynamic sizing
const clampElementPositions = (elements, canvasWidth = 794, canvasHeight = 1123) => {
    return elements.map(element => {
        let newX = element.x;
        let newY = element.y;
        // Clamp X position (laisser au moins 5px visible)
        const minVisibleWidth = Math.min(15, element.width * 0.3);
        if (newX < 0)
            newX = 0;
        if (newX + minVisibleWidth > canvasWidth)
            newX = Math.max(0, canvasWidth - minVisibleWidth);
        // Clamp Y position (laisser au moins 5px visible)
        const minVisibleHeight = Math.min(10, element.height * 0.3);
        if (newY < 0)
            newY = 0;
        if (newY + minVisibleHeight > canvasHeight)
            newY = Math.max(0, canvasHeight - minVisibleHeight);
        if (newX !== element.x || newY !== element.y) {
            return { ...element, x: newX, y: newY };
        }
        return element;
    });
};
// Fonction helper pour réparer les propriétés des éléments product_table
const repairProductTableProperties = (elements) => {
    const defaultProperties = {
        // Fonctionnalités de base
        showHeaders: true,
        showBorders: true,
        showAlternatingRows: true,
        showSku: true,
        showDescription: true,
        showQuantity: true,
        // Style et apparence
        fontSize: 11,
        currency: '€',
        tableStyle: 'default',
        // Alignements
        textAlign: 'left',
        verticalAlign: 'top',
        // Couleurs
        backgroundColor: '#ffffff',
        headerBackgroundColor: '#f9fafb',
        headerTextColor: '#111827',
        alternateRowColor: '#f9fafb',
        borderColor: '#e5e7eb',
        textColor: '#374151'
    };
    return elements.map(element => {
        if (element.type !== 'product_table')
            return element;
        const repairedElement = { ...element };
        // Ajouter les propriétés manquantes
        Object.keys(defaultProperties).forEach(prop => {
            if (!(prop in repairedElement)) {
                repairedElement[prop] = defaultProperties[prop];
            }
        });
        // Validation des booléens
        const booleanProps = ['showHeaders', 'showBorders', 'showAlternatingRows', 'showSku', 'showDescription', 'showQuantity'];
        booleanProps.forEach(prop => {
            if (typeof repairedElement[prop] !== 'boolean') {
                repairedElement[prop] = defaultProperties[prop];
            }
        });
        // Validation des nombres
        const numberProps = ['fontSize'];
        numberProps.forEach(prop => {
            if (typeof repairedElement[prop] !== 'number') {
                repairedElement[prop] = defaultProperties[prop];
            }
        });
        // Validation des alignements
        const validHorizontalAligns = ['left', 'center', 'right'];
        if (!validHorizontalAligns.includes(repairedElement.textAlign)) {
            repairedElement.textAlign = defaultProperties.textAlign;
        }
        const validVerticalAligns = ['top', 'middle', 'bottom'];
        if (!validVerticalAligns.includes(repairedElement.verticalAlign)) {
            repairedElement.verticalAlign = defaultProperties.verticalAlign;
        }
        // Validation des couleurs (format hexadécimal)
        const colorProperties = ['backgroundColor', 'headerBackgroundColor', 'alternateRowColor', 'borderColor', 'headerTextColor', 'textColor'];
        colorProperties.forEach(prop => {
            if (repairedElement[prop] && !/^#[0-9A-Fa-f]{6}$/.test(repairedElement[prop])) {
                repairedElement[prop] = defaultProperties[prop];
            }
        });
        // Validation de la devise
        if (!repairedElement.currency || typeof repairedElement.currency !== 'string') {
            repairedElement.currency = defaultProperties.currency;
        }
        return repairedElement;
    });
};
// État initial
const initialCanvasState = {
    zoom: 100,
    pan: { x: 0, y: 0 },
    showGrid: false,
    gridSize: 20,
    snapToGrid: true,
    backgroundColor: '#ffffff'
};
const initialSelectionState = {
    selectedElements: [],
    isSelecting: false
};
const initialDragState = {
    isDragging: false,
    draggedElements: []
};
const initialHistoryState = {
    past: [],
    present: {
        elements: [],
        canvas: initialCanvasState,
        selection: initialSelectionState,
        drag: initialDragState,
        mode: 'select',
        template: {
            isNew: true,
            isModified: false,
            isSaving: false,
            isLoading: true // ✅ Start as loading
        },
        previewMode: 'editor',
        history: {
            past: [],
            present: null,
            future: [],
            canUndo: false,
            canRedo: false
        }
    },
    future: [],
    canUndo: false,
    canRedo: false
};
const initialState = {
    elements: [],
    canvas: initialCanvasState,
    selection: initialSelectionState,
    drag: initialDragState,
    mode: 'select',
    template: {
        isNew: true,
        isModified: false,
        isSaving: false,
        isLoading: true,
        name: 'Nouveau template',
        description: '',
        tags: [],
        canvasWidth: 794,
        canvasHeight: 1123,
        marginTop: 28,
        marginBottom: 28,
        showGuides: true,
        snapToGrid: false
    },
    previewMode: 'editor',
    history: initialHistoryState
};
// Reducer
function builderReducer(state, action) {
    var _a, _b;
    switch (action.type) {
        case 'ADD_ELEMENT': {
            return {
                ...state,
                elements: [...state.elements, action.payload],
                template: {
                    ...state.template,
                    isModified: true
                },
                history: updateHistory(state, {
                    ...state,
                    elements: [...state.elements, action.payload]
                })
            };
        }
        case 'UPDATE_ELEMENT': {
            // ✅ BUGFIX-003: Comprehensive property preservation
            // Ensure ALL existing properties are retained when updating
            const updateElement = (element) => {
                if (element.id !== action.payload.id)
                    return element;
                // Merge updates while preserving all existing properties
                const updated = {
                    ...element,
                    ...action.payload.updates,
                    updatedAt: new Date() // Always update timestamp
                };
                return updated;
            };
            const updatedElements = state.elements.map(updateElement);
            return {
                ...state,
                elements: updatedElements,
                template: {
                    ...state.template,
                    isModified: true
                },
                history: updateHistory(state, {
                    ...state,
                    elements: updatedElements
                })
            };
        }
        case 'REMOVE_ELEMENT': {
            return {
                ...state,
                elements: state.elements.filter(el => el.id !== action.payload),
                selection: {
                    ...state.selection,
                    selectedElements: state.selection.selectedElements.filter(id => id !== action.payload)
                },
                template: {
                    ...state.template,
                    isModified: true
                },
                history: updateHistory(state, {
                    ...state,
                    elements: state.elements.filter(el => el.id !== action.payload),
                    selection: {
                        ...state.selection,
                        selectedElements: state.selection.selectedElements.filter(id => id !== action.payload)
                    }
                })
            };
        }
        case 'SET_ELEMENTS': {
            return {
                ...state,
                elements: action.payload,
                history: updateHistory(state, { ...state, elements: action.payload })
            };
        }
        case 'SET_SELECTION': {
            return {
                ...state,
                selection: {
                    ...state.selection,
                    selectedElements: action.payload,
                    selectionBounds: calculateSelectionBounds(action.payload, state.elements)
                }
            };
        }
        case 'CLEAR_SELECTION': {
            return {
                ...state,
                selection: {
                    ...state.selection,
                    selectedElements: [],
                    selectionBounds: undefined
                }
            };
        }
        case 'SET_CANVAS': {
            return {
                ...state,
                canvas: { ...state.canvas, ...action.payload }
            };
        }
        case 'SET_MODE': {
            return {
                ...state,
                mode: action.payload
            };
        }
        case 'SET_DRAG_STATE': {
            return {
                ...state,
                drag: { ...state.drag, ...action.payload }
            };
        }
        case 'SET_PREVIEW_MODE': {
            return {
                ...state,
                previewMode: action.payload
            };
        }
        case 'SET_ORDER_ID': {
            return {
                ...state,
                orderId: action.payload
            };
        }
        case 'UNDO': {
            if (!state.history.canUndo)
                return state;
            const previousState = state.history.past[state.history.past.length - 1];
            return {
                ...previousState,
                history: {
                    past: state.history.past.slice(0, -1),
                    present: previousState,
                    future: [state, ...state.history.future],
                    canUndo: state.history.past.length > 1,
                    canRedo: true
                }
            };
        }
        case 'REDO': {
            if (!state.history.canRedo)
                return state;
            const nextState = state.history.future[0];
            return {
                ...nextState,
                history: {
                    past: [...state.history.past, state],
                    present: nextState,
                    future: state.history.future.slice(1),
                    canUndo: true,
                    canRedo: state.history.future.length > 1
                }
            };
        }
        case 'RESET': {
            return initialState;
        }
        case 'SAVE_TEMPLATE': {
            return {
                ...state,
                template: {
                    ...state.template,
                    isNew: false,
                    isModified: false,
                    isSaving: false,
                    lastSaved: new Date(),
                    id: ((_a = action.payload) === null || _a === void 0 ? void 0 : _a.id) || state.template.id,
                    name: ((_b = action.payload) === null || _b === void 0 ? void 0 : _b.name) || state.template.name
                }
            };
        }
        case 'SET_TEMPLATE_MODIFIED': {
            return {
                ...state,
                template: {
                    ...state.template,
                    isModified: action.payload
                }
            };
        }
        case 'SET_TEMPLATE_SAVING':
            return {
                ...state,
                template: {
                    ...state.template,
                    isSaving: action.payload
                }
            };
        case 'UPDATE_TEMPLATE_SETTINGS':
            return {
                ...state,
                template: {
                    ...state.template,
                    ...action.payload,
                    isModified: true
                }
            };
        case 'SET_TEMPLATE_LOADING':
            return {
                ...state,
                template: {
                    ...state.template,
                    isLoading: action.payload
                }
            };
        case 'TOGGLE_GUIDES':
            return {
                ...state,
                template: {
                    ...state.template,
                    showGuides: !state.template.showGuides
                    // Note: isModified is NOT set to true for guides toggle
                }
            };
        case 'LOAD_TEMPLATE': {
            const rawElements = action.payload.elements || [];
            const repairedElements = repairProductTableProperties(rawElements);
            // Ne pas convertir, garder les PX directement
            const clampedElements = clampElementPositions(repairedElements);
            // Garder les dimensions du canvas si présentes
            const canvasData = action.payload.canvas ?
                { ...state.canvas, ...action.payload.canvas } :
                state.canvas;
            const newState = {
                ...state,
                elements: clampedElements,
                canvas: canvasData,
                template: {
                    ...state.template,
                    id: action.payload.id,
                    name: action.payload.name,
                    isNew: false,
                    isModified: false,
                    isSaving: false,
                    isLoading: false,
                    lastSaved: action.payload.lastSaved
                },
                history: updateHistory(state, {
                    ...state,
                    elements: clampedElements,
                    canvas: canvasData
                })
            };
            return newState;
        }
        case 'NEW_TEMPLATE': {
            return {
                ...initialState,
                template: {
                    isNew: true,
                    isModified: false,
                    isSaving: false,
                    isLoading: false // ✅ New template ready immediately
                }
            };
        }
        default: {
            return state;
        }
    }
}
// Fonctions utilitaires
function updateHistory(currentState, newState) {
    // ✅ BUGFIX-011: Deep copy the state before storing in history to ensure immutability
    const stateCopy = {
        ...currentState,
        elements: currentState.elements.map(el => ({ ...el })),
        canvas: { ...currentState.canvas },
        selection: { ...currentState.selection, selectedElements: [...currentState.selection.selectedElements] },
        drag: { ...currentState.drag },
        template: { ...currentState.template },
        history: currentState.history // Don't deep copy history recursively
    };
    return {
        past: [...currentState.history.past, stateCopy],
        present: newState,
        future: [],
        canUndo: true,
        canRedo: false
    };
}
function calculateSelectionBounds(selectedIds, elements) {
    if (selectedIds.length === 0)
        return undefined;
    const selectedElements = elements.filter(el => selectedIds.includes(el.id));
    if (selectedElements.length === 0)
        return undefined;
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    selectedElements.forEach(el => {
        minX = Math.min(minX, el.x);
        minY = Math.min(minY, el.y);
        maxX = Math.max(maxX, el.x + el.width);
        maxY = Math.max(maxY, el.y + el.height);
    });
    return {
        x: minX,
        y: minY,
        width: maxX - minX,
        height: maxY - minY
    };
}
const BuilderContext = (0,react.createContext)(undefined);
function BuilderProvider({ children, initialState: initialStateProp }) {
    // fusionne l'état par défaut avec l'éventuel initialState passé en prop
    const mergedInitialState = {
        ...initialState,
        ...(initialStateProp || {})
    };
    const [state, dispatch] = (0,react.useReducer)(builderReducer, mergedInitialState);
    const canvasSettings = CanvasSettingsContext_useCanvasSettings();
    // ✅ CORRECTION: Flags pour éviter les boucles infinies
    const zoomInitializedRef = (0,react.useRef)(false);
    const gridInitializedRef = (0,react.useRef)(false);
    // Appliquer les paramètres de zoom depuis Canvas Settings au démarrage
    (0,react.useEffect)(() => {
        // Appliquer le zoom par défaut depuis les paramètres UNIQUEMENT au démarrage
        // Le zoom initial du state est 100, donc appliquer seulement si différent et prêt
        const initialZoom = 100; // Valeur initiale du state
        if (canvasSettings.isReady && canvasSettings.zoomDefault !== initialZoom && !zoomInitializedRef.current) {
            zoomInitializedRef.current = true;
            dispatch({
                type: 'SET_CANVAS',
                payload: {
                    zoom: Math.max(canvasSettings.zoomMin, Math.min(canvasSettings.zoomDefault, canvasSettings.zoomMax))
                }
            });
        }
    }, [canvasSettings.zoomDefault, canvasSettings.zoomMax, canvasSettings.zoomMin, canvasSettings.isReady]);
    // Synchroniser les paramètres de grille depuis CanvasSettingsContext (uniquement à l'initialisation)
    (0,react.useEffect)(() => {
        if (!canvasSettings.isReady || gridInitializedRef.current)
            return;
        const updates = {};
        // Ne synchroniser que si c'est la première fois ou si les paramètres ont changé dans les settings
        // mais pas si l'utilisateur a changé manuellement l'état
        if (canvasSettings.gridSize !== state.canvas.gridSize) {
            updates.gridSize = canvasSettings.gridSize;
        }
        // Pour showGrid et snapToGrid, ne synchroniser que si l'état actuel correspond aux paramètres par défaut
        // (c'est-à-dire au chargement initial)
        if (state.canvas.showGrid === canvasSettings.gridShow && canvasSettings.gridShow !== state.canvas.showGrid) {
            updates.showGrid = canvasSettings.gridShow;
        }
        if (state.canvas.snapToGrid === canvasSettings.gridSnapEnabled && canvasSettings.gridSnapEnabled !== state.canvas.snapToGrid) {
            updates.snapToGrid = canvasSettings.gridSnapEnabled;
        }
        if (Object.keys(updates).length > 0) {
            gridInitializedRef.current = true;
            dispatch({ type: 'SET_CANVAS', payload: updates });
        }
    }, [canvasSettings.gridSize, canvasSettings.gridShow, canvasSettings.gridSnapEnabled, canvasSettings.isReady, state.canvas.gridSize, state.canvas.showGrid, state.canvas.snapToGrid]);
    // ✅ DISABLED: Template loading is now EXCLUSIVELY handled by useTemplate hook
    // which reads template_id from URL/localized data and calls AJAX GET
    // This prevents duplicate/race condition loads which caused double canvas renders
    // Previously: BuilderContext useEffect loaded → dispatch → Canvas renders
    //             useTemplate hook also loaded → dispatch → Canvas renders AGAIN (1/10th sec later)
    // Now: Only useTemplate.ts loads the template, ensuring single source of truth
    // Écouteur pour le chargement de template via API globale
    (0,react.useEffect)(() => {
        const handleLoadTemplate = (event) => {
            const templateData = event.detail;
            if (templateData) {
                dispatch({
                    type: 'LOAD_TEMPLATE',
                    payload: templateData
                });
            }
            else {
                debugWarn('[BuilderContext] No template data in event detail');
            }
        };
        document.addEventListener('pdfBuilderLoadTemplate', handleLoadTemplate, { passive: true });
        return () => {
            document.removeEventListener('pdfBuilderLoadTemplate', handleLoadTemplate);
        };
    }, []);
    // Actions helpers
    const addElement = (element) => {
        dispatch({ type: 'ADD_ELEMENT', payload: element });
    };
    const updateElement = (id, updates) => {
        dispatch({ type: 'UPDATE_ELEMENT', payload: { id, updates } });
    };
    const removeElement = (id) => {
        dispatch({ type: 'REMOVE_ELEMENT', payload: id });
    };
    const setSelection = (ids) => {
        dispatch({ type: 'SET_SELECTION', payload: ids });
    };
    const clearSelection = () => {
        dispatch({ type: 'CLEAR_SELECTION' });
    };
    const setCanvas = (canvas) => {
        dispatch({ type: 'SET_CANVAS', payload: canvas });
    };
    const setMode = (mode) => {
        dispatch({ type: 'SET_MODE', payload: mode });
    };
    const undo = () => {
        dispatch({ type: 'UNDO' });
    };
    const redo = () => {
        dispatch({ type: 'REDO' });
    };
    const reset = () => {
        dispatch({ type: 'RESET' });
    };
    const toggleGrid = () => {
        setCanvas({ showGrid: !state.canvas.showGrid });
    };
    const toggleGuides = () => {
        dispatch({ type: 'TOGGLE_GUIDES' });
    };
    const value = {
        state,
        dispatch,
        addElement,
        updateElement,
        removeElement,
        setSelection,
        clearSelection,
        setCanvas,
        setMode,
        undo,
        redo,
        reset,
        toggleGrid: toggleGrid,
        toggleGuides: toggleGuides
    };
    return ((0,jsx_runtime.jsx)(BuilderContext.Provider, { value: value, children: children }));
}
// Hook pour utiliser le contexte
function useBuilder() {
    const context = (0,react.useContext)(BuilderContext);
    if (context === undefined) {
        throw new Error('useBuilder must be used within a BuilderProvider');
    }
    return context;
}
// Hook spécialisé pour les éléments
function useElements() {
    const { state, addElement, updateElement, removeElement } = useBuilder();
    return {
        elements: state.elements,
        addElement,
        updateElement,
        removeElement,
        getElement: (id) => state.elements.find(el => el.id === id),
        getElementsByType: (type) => state.elements.filter(el => el.type === type)
    };
}
// Hook spécialisé pour la sélection
function useSelection() {
    const { state, setSelection, clearSelection } = useBuilder();
    return {
        selectedElements: state.selection.selectedElements,
        selectionBounds: state.selection.selectionBounds,
        isSelecting: state.selection.isSelecting,
        setSelection,
        clearSelection,
        isSelected: (id) => state.selection.selectedElements.includes(id),
        toggleSelection: (id) => {
            const isSelected = state.selection.selectedElements.includes(id);
            if (isSelected) {
                setSelection(state.selection.selectedElements.filter(selectedId => selectedId !== id));
            }
            else {
                setSelection([...state.selection.selectedElements, id]);
            }
        }
    };
}
// Hook spécialisé pour le canvas
function useCanvas() {
    const { state, setCanvas } = useBuilder();
    const canvasSettings = useCanvasSettings();
    return {
        canvas: state.canvas,
        setCanvas,
        zoomIn: () => setCanvas({ zoom: Math.min(state.canvas.zoom * 1.2, canvasSettings.zoomMax) }),
        zoomOut: () => setCanvas({ zoom: Math.max(state.canvas.zoom / 1.2, canvasSettings.zoomMin) }),
        setZoom: (zoom) => setCanvas({ zoom: Math.max(canvasSettings.zoomMin, Math.min(zoom, canvasSettings.zoomMax)) }),
        resetZoom: () => setCanvas({ zoom: Math.max(canvasSettings.zoomMin, Math.min(canvasSettings.zoomDefault, canvasSettings.zoomMax)) }),
        toggleGrid: () => setCanvas({ showGrid: !state.canvas.showGrid }),
        setBackgroundColor: (color) => setCanvas({ backgroundColor: color })
    };
}


;// ./assets/js/pdf-builder-react/hooks/useCanvasSettings.ts


/**
 * Hook pour accéder aux paramètres du canvas
 * Retourne tous les paramètres canvas depuis les options WordPress
 * Se met à jour automatiquement quand les paramètres changent
 *
 * @returns {Object} Les paramètres du canvas
 */
const useCanvasSettings_useCanvasSettings = () => {
    const [settings, setSettings] = (0,react.useState)(() => window.pdfBuilderCanvasSettings || getDefaultCanvasSettings());
    const [isLoading, setIsLoading] = (0,react.useState)(false);
    // Écouter les changements de paramètres
    (0,react.useEffect)(() => {
        let isMounted = true;
        const fetchSettings = async () => {
            var _a, _b;
            if (isLoading)
                return; // Éviter les appels multiples
            try {
                setIsLoading(true);
                const ajaxUrl = ((_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.ajaxUrl) || '/wp-admin/admin-ajax.php';
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'pdf_builder_get_canvas_settings',
                        nonce: ((_b = window.pdfBuilderData) === null || _b === void 0 ? void 0 : _b.nonce) || ''
                    })
                });
                if (!isMounted)
                    return;
                const data = await response.json();
                if (data.success && data.data) {
                    window.pdfBuilderCanvasSettings = {
                        ...window.pdfBuilderCanvasSettings,
                        ...data.data
                    };
                    setSettings(window.pdfBuilderCanvasSettings);
                }
                else {
                    debugWarn('REACT: Invalid AJAX response:', data);
                }
            }
            catch (error) {
                if (isMounted) {
                    debugWarn('REACT: Failed to fetch updated settings:', error);
                }
            }
            finally {
                if (isMounted) {
                    setIsLoading(false);
                }
            }
        };
        const handleSettingsUpdate = () => {
            if (isMounted) {
                setSettings(window.pdfBuilderCanvasSettings);
            }
        };
        const handleStorageChange = async (event) => {
            if (event.key === 'pdfBuilderSettingsUpdated' && isMounted) {
                await fetchSettings();
            }
        };
        // Ne fetch que si les settings ne sont pas déjà chargés
        if (!window.pdfBuilderCanvasSettings) {
            fetchSettings();
        }
        // Check if settings were updated while this tab was closed
        if (localStorage.getItem('pdfBuilderSettingsUpdated')) {
            fetchSettings();
        }
        window.addEventListener('canvasSettingsUpdated', handleSettingsUpdate);
        window.addEventListener('storage', handleStorageChange);
        return () => {
            isMounted = false;
            window.removeEventListener('canvasSettingsUpdated', handleSettingsUpdate);
            window.removeEventListener('storage', handleStorageChange);
        };
    }, []); // Dépendances vides pour un seul appel au montage
    return settings;
};
/**
 * Hook pour accéder à un paramètre canvas spécifique
 *
 * @param {string} key - Clé du paramètre
 * @param {unknown} defaultValue - Valeur par défaut
 * @returns {unknown} La valeur du paramètre
 */
const useCanvasSetting = (key, defaultValue = null) => {
    const settings = useCanvasSettings_useCanvasSettings();
    return (0,react.useMemo)(() => {
        const value = key in settings ? settings[key] : defaultValue;
        return value;
    }, [key, settings, defaultValue]);
};
/**
 * Hook pour accéder aux dimensions du canvas
 *
 * @returns {Object} Les dimensions (width, height, unit, orientation)
 */
const useCanvasDimensions = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        width: settings.default_canvas_width || 794,
        height: settings.default_canvas_height || 1123,
        unit: settings.default_canvas_unit || 'px',
        orientation: settings.default_orientation || 'portrait',
    }), [settings]);
};
/**
 * Hook pour accéder aux marges du canvas
 *
 * @returns {Object} Les marges (top, right, bottom, left)
 */
const useCanvasMargins = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        top: settings.margin_top || 28,
        right: settings.margin_right || 28,
        bottom: settings.margin_bottom || 10,
        left: settings.margin_left || 10,
    }), [settings]);
};
/**
 * Hook pour accéder aux paramètres de grille
 *
 * @returns {Object} Les paramètres de grille
 */
const useGridSettings = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        show: settings.show_grid || false,
        size: settings.grid_size || 10,
        color: settings.grid_color || '#e0e0e0',
        snapEnabled: settings.snap_to_grid || false,
        snapTolerance: settings.snap_tolerance || 5,
    }), [settings]);
};
/**
 * Hook pour accéder aux paramètres de zoom
 *
 * @returns {Object} Les paramètres de zoom
 */
const useZoomSettings = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        default: settings.default_zoom || 100,
        step: settings.zoom_step || 25,
        min: settings.min_zoom || 10,
        max: settings.max_zoom || 500,
        wheelEnabled: settings.zoom_with_wheel || false,
    }), [settings]);
};
/**
 * Hook pour accéder aux paramètres de sélection
 *
 * @returns {Object} Les paramètres de sélection
 */
const useSelectionSettings = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        multiSelect: settings.multi_select || false,
        copyPaste: settings.copy_paste_enabled || false,
        rotation: settings.enable_rotation || false,
        rotationStep: settings.rotation_step || 15,
        showHandles: settings.show_resize_handles || false,
        handleSize: settings.handle_size || 8,
    }), [settings]);
};
/**
 * Hook pour accéder aux paramètres d'export
 *
 * @returns {Object} Les paramètres d'export
 */
const useExportSettings = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        quality: settings.export_quality || 'print',
        format: settings.export_format || 'pdf',
        compressImages: settings.compress_images || true,
        imageQuality: settings.image_quality || 85,
        maxImageSize: settings.max_image_size || 2048,
        includeMetadata: settings.include_metadata || true,
    }), [settings]);
};
/**
 * Hook pour accéder aux paramètres d'historique
 *
 * @returns {Object} Les paramètres d'historique
 */
const useHistorySettings = () => {
    const settings = useCanvasSettings_useCanvasSettings();
    return useMemo(() => ({
        undoLevels: settings.undo_levels || 50,
        redoLevels: settings.redo_levels || 50,
    }), [settings]);
};
/**
 * Retourne les paramètres par défaut du canvas
 *
 * @returns {Object} Paramètres par défaut
 */
const getDefaultCanvasSettings = () => ({
    default_canvas_width: 794,
    default_canvas_height: 1123,
    default_canvas_unit: 'px',
    default_orientation: 'portrait',
    canvas_background_color: '#ffffff',
    canvas_show_transparency: false,
    container_background_color: '#f8f9fa',
    container_show_transparency: false,
    margin_top: 28,
    margin_right: 28,
    margin_bottom: 10,
    margin_left: 10,
    show_margins: false,
    show_grid: false,
    grid_size: 10,
    grid_color: '#e0e0e0',
    snap_to_grid: false,
    snap_to_elements: false,
    snap_tolerance: 5,
    show_guides: false,
    default_zoom: 100,
    zoom_step: 25,
    min_zoom: 10,
    max_zoom: 500,
    zoom_with_wheel: false,
    pan_with_mouse: false,
    show_resize_handles: false,
    handle_size: 8,
    handle_color: '#007cba',
    enable_rotation: false,
    rotation_step: 15,
    multi_select: false,
    copy_paste_enabled: false,
    export_quality: 'print',
    export_format: 'pdf',
    compress_images: true,
    image_quality: 85,
    max_image_size: 2048,
    include_metadata: true,
    pdf_author: 'PDF Builder Pro',
    pdf_subject: '',
    auto_crop: false,
    embed_fonts: true,
    optimize_for_web: true,
    enable_hardware_acceleration: true,
    limit_fps: true,
    max_fps: 60,
    auto_save_enabled: true,
    auto_save_interval: 5,
    auto_save_versions: 10,
    undo_levels: 50,
    redo_levels: 50,
    enable_keyboard_shortcuts: true,
    debug_mode: false,
    show_fps: false,
});

;// ./assets/js/pdf-builder-react/hooks/useCanvasDrop.ts



const useCanvasDrop = ({ canvasRef, canvasWidth, canvasHeight, elements, dragEnabled = true }) => {
    const { state, dispatch } = useBuilder();
    const [isDragOver, setIsDragOver] = (0,react.useState)(false);
    // ✅ Validation des données de drag
    const validateDragData = (0,react.useCallback)((data) => {
        if (!data || typeof data !== 'object')
            return false;
        const dragData = data;
        return (typeof dragData.type === 'string' &&
            typeof dragData.label === 'string' &&
            typeof dragData.defaultProps === 'object' &&
            dragData.defaultProps !== null);
    }, []);
    // ✅ Calcul correct des coordonnées avec zoom/pan
    const calculateDropPosition = (0,react.useCallback)((clientX, clientY, elementWidth = 100, elementHeight = 50) => {
        const canvas = canvasRef.current;
        if (!canvas) {
            throw new Error('Canvas ref not available');
        }
        const rect = canvas.getBoundingClientRect();
        // Validation du rectangle canvas
        if (rect.width <= 0 || rect.height <= 0) {
            throw new Error('Invalid canvas dimensions');
        }
        // Calcul des coordonnées dans l'espace canvas (avant transformation)
        const canvasX = clientX - rect.left;
        const canvasY = clientY - rect.top;
        // Validation des coordonnées
        if (canvasX < 0 || canvasY < 0 || canvasX > rect.width || canvasY > rect.height) {
        }
        // Appliquer la transformation inverse (zoom/pan)
        // Note: zoom est en pourcentage (100 = 100%), donc diviser par 100
        const zoomScale = state.canvas.zoom / 100;
        // Position dans l'espace canvas transformé
        const transformedX = (canvasX - state.canvas.pan.x) / zoomScale;
        const transformedY = (canvasY - state.canvas.pan.y) / zoomScale;
        // Centrer l'élément sur le point de drop
        const centeredX = Math.max(0, transformedX - elementWidth / 2);
        const centeredY = Math.max(0, transformedY - elementHeight / 2);
        // S'assurer que l'élément reste dans les limites du canvas
        const clampedX = Math.max(0, Math.min(centeredX, canvasWidth - elementWidth));
        const clampedY = Math.max(0, Math.min(centeredY, canvasHeight - elementHeight));
        return {
            x: clampedX,
            y: clampedY,
            originalCanvasX: canvasX,
            originalCanvasY: canvasY,
            transformedX,
            transformedY
        };
    }, [canvasRef, canvasWidth, canvasHeight, state.canvas]);
    // ✅ Génération d'ID unique pour les éléments
    const generateElementId = (0,react.useCallback)((type) => {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substr(2, 9);
        return `element_${type}_${timestamp}_${random}`;
    }, []);
    // ✅ Création d'élément avec validation
    const createElementFromDragData = (0,react.useCallback)((dragData, position) => {
        const elementId = generateElementId(dragData.type);
        // S'assurer que width et height sont définis
        const width = dragData.defaultProps.width || 100;
        const height = dragData.defaultProps.height || 50;
        // Fusion des propriétés par défaut avec les propriétés calculées
        const element = {
            id: elementId,
            type: dragData.type,
            // Propriétés par défaut (peuvent être overriden par position)
            ...dragData.defaultProps,
            // Position calculée (override x, y des defaultProps)
            x: position.x,
            y: position.y,
            width,
            height,
            // Propriétés système requises
            visible: true,
            locked: false,
            createdAt: new Date(),
            updatedAt: new Date()
        };
        return element;
    }, [generateElementId]);
    const handleDrop = (0,react.useCallback)((e) => {
        if (!dragEnabled) {
            debugLog('[CanvasDrop] Drop ignored - drag disabled');
            return;
        }
        e.preventDefault();
        setIsDragOver(false);
        debugLog('[CanvasDrop] Processing drop event');
        try {
            // Parsing des données de drag
            const rawData = e.dataTransfer.getData('application/json');
            if (!rawData) {
                throw new Error('No drag data received');
            }
            const dragData = JSON.parse(rawData);
            debugLog(`[CanvasDrop] Parsed drag data: ${dragData.type} (${dragData.label})`);
            // Validation des données
            if (!validateDragData(dragData)) {
                throw new Error('Invalid drag data structure');
            }
            // Calcul de la position avec zoom/pan
            const elementWidth = dragData.defaultProps.width || 100;
            const elementHeight = dragData.defaultProps.height || 50;
            const position = calculateDropPosition(e.clientX, e.clientY, elementWidth, elementHeight);
            debugLog(`[CanvasDrop] Calculated drop position: (${position.x}, ${position.y}) from client coords (${e.clientX}, ${e.clientY})`);
            // Création de l'élément
            const newElement = createElementFromDragData(dragData, position);
            debugLog(`[CanvasDrop] Created element: ${newElement.id} (${newElement.type})`);
            // Vérification des conflits d'ID
            const existingElement = elements.find(el => el.id === newElement.id);
            if (existingElement) {
                newElement.id = generateElementId(dragData.type);
                debugWarn(`[CanvasDrop] ID conflict resolved, new ID: ${newElement.id}`);
            }
            // Ajout au state
            dispatch({ type: 'ADD_ELEMENT', payload: newElement });
            debugLog(`[CanvasDrop] Element added to canvas successfully`);
        }
        catch (error) {
            debug_debugError(`[CanvasDrop] Drop failed:`, error);
        }
    }, [validateDragData, calculateDropPosition, createElementFromDragData, elements, dispatch, generateElementId, dragEnabled]);
    const handleDragOver = (0,react.useCallback)((e) => {
        if (!dragEnabled) {
            debugLog('[CanvasDrop] Drag over ignored - drag disabled');
            return;
        }
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        if (!isDragOver) {
            debugLog('[CanvasDrop] Drag over started');
            setIsDragOver(true);
        }
    }, [isDragOver, dragEnabled]);
    const handleDragLeave = (0,react.useCallback)((e) => {
        if (!dragEnabled) {
            debugLog('[CanvasDrop] Drag leave ignored - drag disabled');
            return;
        }
        // Simple check - if we have a relatedTarget, assume drag is leaving
        // This is a simplified approach to avoid DOM type issues
        if (e.relatedTarget) {
            debugLog('[CanvasDrop] Drag leave detected');
            setIsDragOver(false);
        }
    }, [dragEnabled]);
    return {
        handleDrop,
        handleDragOver,
        handleDragLeave,
        isDragOver
    };
};

;// ./assets/js/pdf-builder-react/hooks/useCanvasInteraction.ts




const useCanvasInteraction = ({ canvasRef, canvasWidth = 794, canvasHeight = 1123 }) => {
    const { state, dispatch } = useBuilder();
    const canvasSettings = CanvasSettingsContext_useCanvasSettings();
    // Déterminer le mode de sélection effectif : si sélection multiple désactivée, forcer le mode 'click'
    const selectionMode = canvasSettings.selectionMultiSelectEnabled ? canvasSettings.canvasSelectionMode : 'click';
    // État pour déclencher le re-rendu du canvas pendant la sélection
    const [selectionUpdateTrigger, setSelectionUpdateTrigger] = (0,react.useState)(0);
    // États pour le drag et resize
    const isDraggingRef = (0,react.useRef)(false);
    const isResizingRef = (0,react.useRef)(false);
    const isRotatingRef = (0,react.useRef)(false);
    const dragStartRef = (0,react.useRef)({}); // Pour drag multiple : positions initiales de tous les éléments
    const dragMouseStartRef = (0,react.useRef)({ x: 0, y: 0 }); // Position souris au début du drag
    const resizeMouseStartRef = (0,react.useRef)({ x: 0, y: 0 }); // Position souris au début du resize
    const rotationMouseStartRef = (0,react.useRef)({ x: 0, y: 0 }); // Position souris au début de la rotation
    const rotationStartRef = (0,react.useRef)({}); // Rotations initiales des éléments
    const selectedElementRef = (0,react.useRef)(null);
    const selectedElementsRef = (0,react.useRef)([]); // ✅ Track locally instead of relying on stale state
    const resizeHandleRef = (0,react.useRef)(null);
    const currentCursorRef = (0,react.useRef)('default');
    // États pour les modes de sélection avancés
    const isSelectingRef = (0,react.useRef)(false); // En cours de sélection lasso/rectangle
    const selectionStartRef = (0,react.useRef)({ x: 0, y: 0 }); // Point de départ de la sélection
    const selectionPointsRef = (0,react.useRef)([]); // Points pour le lasso
    const selectionRectRef = (0,react.useRef)({ x: 0, y: 0, width: 0, height: 0 }); // Rectangle de sélection
    // Refs pour les event listeners globaux pendant la sélection
    const globalMouseMoveRef = (0,react.useRef)(null);
    const globalMouseUpRef = (0,react.useRef)(null);
    // ✅ OPTIMISATION FLUIDITÉ: requestAnimationFrame pour synchroniser avec le refresh rate
    const rafIdRef = (0,react.useRef)(null);
    const pendingDragUpdateRef = (0,react.useRef)(null);
    const pendingRotationUpdateRef = (0,react.useRef)(null);
    // ✅ CORRECTION 5: Dernier state connu pour éviter closure stale
    const lastKnownStateRef = (0,react.useRef)(state);
    // Fonctions pour gérer les événements globaux pendant la sélection
    const startGlobalSelectionListeners = (0,react.useCallback)(() => {
        if (globalMouseMoveRef.current || globalMouseUpRef.current)
            return; // Déjà actifs
        globalMouseMoveRef.current = (event) => {
            const canvas = canvasRef.current;
            if (!canvas)
                return;
            const rect = canvas.getBoundingClientRect();
            const zoomScale = state.canvas.zoom / 100;
            // Calcul des coordonnées même si la souris est hors du canvas
            const canvasRelativeX = event.clientX - rect.left;
            const canvasRelativeY = event.clientY - rect.top;
            const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
            const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;
            // Mettre à jour la sélection
            if (selectionMode === 'lasso') {
                selectionPointsRef.current.push({ x, y });
                setSelectionUpdateTrigger(prev => prev + 1);
            }
            else if (selectionMode === 'rectangle') {
                const startX = Math.min(selectionStartRef.current.x, x);
                const startY = Math.min(selectionStartRef.current.y, y);
                const width = Math.abs(x - selectionStartRef.current.x);
                const height = Math.abs(y - selectionStartRef.current.y);
                selectionRectRef.current = { x: startX, y: startY, width, height };
                setSelectionUpdateTrigger(prev => prev + 1);
            }
        };
        globalMouseUpRef.current = () => {
            stopGlobalSelectionListeners();
            // Terminer la sélection directement ici
            if (isSelectingRef.current) {
                let selectedElementIds = [];
                if (selectionMode === 'lasso' && selectionPointsRef.current.length > 2) {
                    // Utiliser la même logique que isElementInLasso
                    selectedElementIds = state.elements
                        .filter(element => {
                        const centerX = element.x + element.width / 2;
                        const centerY = element.y + element.height / 2;
                        // Logique de isPointInPolygon dupliquée
                        let inside = false;
                        const polygon = selectionPointsRef.current;
                        for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                            const xi = polygon[i].x, yi = polygon[i].y;
                            const xj = polygon[j].x, yj = polygon[j].y;
                            if (((yi > centerY) !== (yj > centerY)) && (centerX < (xj - xi) * (centerY - yi) / (yj - yi) + xi)) {
                                inside = !inside;
                            }
                        }
                        return inside;
                    })
                        .map(element => element.id);
                }
                else if (selectionMode === 'rectangle' && selectionRectRef.current.width > 0 && selectionRectRef.current.height > 0) {
                    // Utiliser la même logique que isElementInRectangle
                    selectedElementIds = state.elements
                        .filter(element => {
                        const elementRight = element.x + element.width;
                        const elementBottom = element.y + element.height;
                        const rectRight = selectionRectRef.current.x + selectionRectRef.current.width;
                        const rectBottom = selectionRectRef.current.y + selectionRectRef.current.height;
                        return !(element.x > rectRight || elementRight < selectionRectRef.current.x || element.y > rectBottom || elementBottom < selectionRectRef.current.y);
                    })
                        .map(element => element.id);
                }
                if (selectedElementIds.length > 0) {
                    dispatch({ type: 'SET_SELECTION', payload: selectedElementIds });
                }
                else {
                    dispatch({ type: 'CLEAR_SELECTION' });
                }
                // Réinitialiser l'état de sélection
                isSelectingRef.current = false;
                selectionPointsRef.current = [];
                selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
            }
        };
        document.addEventListener('mousemove', globalMouseMoveRef.current, { passive: false });
        document.addEventListener('mouseup', globalMouseUpRef.current, { passive: false });
    }, [canvasRef, state.canvas.zoom, state.canvas.pan, state.elements, selectionMode, dispatch]);
    const stopGlobalSelectionListeners = (0,react.useCallback)(() => {
        if (globalMouseMoveRef.current) {
            document.removeEventListener('mousemove', globalMouseMoveRef.current);
            globalMouseMoveRef.current = null;
        }
        if (globalMouseUpRef.current) {
            document.removeEventListener('mouseup', globalMouseUpRef.current);
            globalMouseUpRef.current = null;
        }
    }, []);
    // ✅ OPTIMISATION FLUIDITÉ: Fonction pour effectuer les updates de drag avec RAF
    const performDragUpdate = (0,react.useCallback)(() => {
        if (!pendingDragUpdateRef.current) {
            rafIdRef.current = null;
            return;
        }
        const { x: currentMouseX, y: currentMouseY } = pendingDragUpdateRef.current;
        const lastState = lastKnownStateRef.current;
        // ✅ MODIFICATION: Gérer le drag multiple
        const selectedIds = lastState.selection.selectedElements;
        if (selectedIds.length === 0) {
            rafIdRef.current = null;
            return;
        }
        // Calculer le delta de déplacement de la souris depuis le début du drag
        const mouseDeltaX = currentMouseX - dragMouseStartRef.current.x;
        const mouseDeltaY = currentMouseY - dragMouseStartRef.current.y;
        // Mettre à jour tous les éléments sélectionnés
        selectedIds.forEach(elementId => {
            const element = lastState.elements.find(el => el.id === elementId);
            if (!element)
                return;
            // Récupérer la position de départ de cet élément spécifique
            const elementStartPos = dragStartRef.current[elementId];
            if (!elementStartPos)
                return;
            // Calculer la nouvelle position en appliquant le delta de la souris à la position de départ
            let finalX = elementStartPos.x + mouseDeltaX;
            let finalY = elementStartPos.y + mouseDeltaY;
            // ✅ AJOUT: Logique d'accrochage à la grille
            if (lastState.canvas.snapToGrid && lastState.canvas.gridSize > 0) {
                const gridSize = lastState.canvas.gridSize;
                const snapTolerance = 5; // Tolérance de 5px pour l'accrochage
                // Calculer la distance à la grille la plus proche
                const nearestGridX = Math.round(finalX / gridSize) * gridSize;
                const nearestGridY = Math.round(finalY / gridSize) * gridSize;
                // Appliquer l'accrochage seulement si on est assez proche de la grille
                if (Math.abs(finalX - nearestGridX) <= snapTolerance) {
                    finalX = nearestGridX;
                }
                if (Math.abs(finalY - nearestGridY) <= snapTolerance) {
                    finalY = nearestGridY;
                }
            }
            // S'assurer que l'élément reste dans les limites du canvas
            const canvasWidthPx = canvasWidth;
            const canvasHeightPx = canvasHeight;
            // Clamp X position (laisser au moins 20px visible)
            const minVisibleWidth = Math.min(50, element.width * 0.3);
            if (finalX < 0)
                finalX = 0;
            if (finalX + minVisibleWidth > canvasWidthPx)
                finalX = canvasWidthPx - minVisibleWidth;
            // Clamp Y position (laisser au moins 20px visible)
            const minVisibleHeight = Math.min(30, element.height * 0.3);
            if (finalY < 0)
                finalY = 0;
            if (finalY + minVisibleHeight > canvasHeightPx)
                finalY = canvasHeightPx - minVisibleHeight;
            // ✅ CORRECTION 6: Améliorer la préservation des propriétés
            const completeUpdates = { x: finalX, y: finalY };
            // ✅ Préserver TOUTES les propriétés
            const elementAsRecord = element;
            Object.keys(elementAsRecord).forEach(key => {
                if (key !== 'x' && key !== 'y' && key !== 'updatedAt') {
                    completeUpdates[key] = elementAsRecord[key];
                }
            });
            // ✅ CRITICAL: Explicitement préserver ces propriétés critiques
            if ('src' in elementAsRecord) {
                completeUpdates.src = elementAsRecord.src;
            }
            if ('logoUrl' in elementAsRecord) {
                completeUpdates.logoUrl = elementAsRecord.logoUrl;
            }
            if ('alignment' in elementAsRecord) {
                completeUpdates.alignment = elementAsRecord.alignment;
            }
            dispatch({
                type: 'UPDATE_ELEMENT',
                payload: {
                    id: elementId,
                    updates: completeUpdates
                }
            });
        });
        pendingDragUpdateRef.current = null;
        rafIdRef.current = null;
    }, [dispatch, canvasWidth, canvasHeight]);
    // ✅ OPTIMISATION FLUIDITÉ: Fonction pour effectuer les updates de rotation avec RAF
    const performRotationUpdate = (0,react.useCallback)(() => {
        if (!pendingRotationUpdateRef.current) {
            rafIdRef.current = null;
            return;
        }
        const { x: currentMouseX, y: currentMouseY } = pendingRotationUpdateRef.current;
        const lastState = lastKnownStateRef.current;
        // ✅ MODIFICATION: Gérer la rotation multiple
        const selectedIds = lastState.selection.selectedElements;
        if (selectedIds.length === 0) {
            rafIdRef.current = null;
            return;
        }
        // Calculer le centre de rotation (centre de la sélection)
        const selectedElements = lastState.elements.filter(el => selectedIds.includes(el.id));
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        selectedElements.forEach(el => {
            minX = Math.min(minX, el.x);
            minY = Math.min(minY, el.y);
            maxX = Math.max(maxX, el.x + el.width);
            maxY = Math.max(maxY, el.y + el.height);
        });
        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;
        // Calculer l'angle de rotation basé sur la position de la souris
        const startAngle = Math.atan2(rotationMouseStartRef.current.y - centerY, rotationMouseStartRef.current.x - centerX);
        const currentAngle = Math.atan2(currentMouseY - centerY, currentMouseX - centerX);
        // Calculer la différence angulaire avec gestion du wrap-around
        let angleDelta = currentAngle - startAngle;
        // Normaliser l'angle entre -π et π pour éviter les sauts
        while (angleDelta > Math.PI)
            angleDelta -= 2 * Math.PI;
        while (angleDelta < -Math.PI)
            angleDelta += 2 * Math.PI;
        // Ajuster la sensibilité de la rotation (moins violent)
        angleDelta *= 1.3; // Multiplier par 1.3 pour une rotation douce mais perceptible
        // Convertir en degrés
        let totalRotationDegrees = (angleDelta * 180) / Math.PI;
        // ✅ AJOUT: Snap magnétique ULTRA SIMPLE - forcer à 0° quand proche
        const zeroSnapTolerance = 8; // 8 degrees (moins agressif)
        // Calculer la rotation actuelle pour chaque élément
        selectedIds.forEach(elementId => {
            const element = lastState.elements.find(el => el.id === elementId);
            if (element) {
                const initialRotation = rotationStartRef.current[elementId] || 0;
                let currentRotation = initialRotation + totalRotationDegrees;
                // Normaliser l'angle entre -180° et 180°
                let normalizedRotation = currentRotation % 360;
                if (normalizedRotation > 180)
                    normalizedRotation -= 360;
                if (normalizedRotation < -180)
                    normalizedRotation += 360;
                // Distance à 0°
                const distanceToZero = Math.abs(normalizedRotation);
                // SI PROCHE DE 0°, FORCER totalRotationDegrees pour que la rotation finale soit 0°
                if (distanceToZero <= zeroSnapTolerance) {
                    // Forcer totalRotationDegrees pour que newRotation = 0
                    totalRotationDegrees = -initialRotation;
                }
            }
        });
        // Mettre à jour la rotation de tous les éléments sélectionnés
        selectedIds.forEach(elementId => {
            const element = lastState.elements.find(el => el.id === elementId);
            if (element) {
                const initialRotation = rotationStartRef.current[elementId] || 0;
                let newRotation = initialRotation + totalRotationDegrees;
                dispatch({
                    type: 'UPDATE_ELEMENT',
                    payload: {
                        id: elementId,
                        updates: { rotation: newRotation }
                    }
                });
            }
        });
        pendingRotationUpdateRef.current = null;
        rafIdRef.current = null;
    }, [dispatch]); // ✅ CORRECTION 3: Throttling pour handleMouseMove - optimisé pour fluidité maximale
    const lastMouseMoveTimeRef = (0,react.useRef)(0);
    const MOUSEMOVE_THROTTLE_MS = 8; // Réduit de 100ms à 8ms pour fluidité maximale (120Hz)
    // Fonction utilitaire pour détecter les poignées de redimensionnement
    // ✅ BUGFIX-018: Consistent margin for hit detection across all element types
    const getResizeHandleAtPosition = (0,react.useCallback)((x, y, selectedIds, elements) => {
        const handleSize = 8;
        const handleMargin = 6; // Consistent margin for all elements
        const selectedElements = elements.filter(el => selectedIds.includes(el.id));
        for (const element of selectedElements) {
            // Calculer les positions des poignées (8 poignées : 4 coins + 4 milieux)
            const handles = [
                // Coins
                { name: 'nw', x: element.x - handleSize / 2, y: element.y - handleSize / 2 },
                { name: 'ne', x: element.x + element.width - handleSize / 2, y: element.y - handleSize / 2 },
                { name: 'sw', x: element.x - handleSize / 2, y: element.y + element.height - handleSize / 2 },
                { name: 'se', x: element.x + element.width - handleSize / 2, y: element.y + element.height - handleSize / 2 },
                // Milieux des côtés
                { name: 'n', x: element.x + element.width / 2 - handleSize / 2, y: element.y - handleSize / 2 },
                { name: 's', x: element.x + element.width / 2 - handleSize / 2, y: element.y + element.height - handleSize / 2 },
                { name: 'w', x: element.x - handleSize / 2, y: element.y + element.height / 2 - handleSize / 2 },
                { name: 'e', x: element.x + element.width - handleSize / 2, y: element.y + element.height / 2 - handleSize / 2 }
            ];
            for (const handle of handles) {
                // Use consistent margin for all element types
                if (x >= handle.x - handleMargin && x <= handle.x + handleSize + handleMargin &&
                    y >= handle.y - handleMargin && y <= handle.y + handleSize + handleMargin) {
                    return { elementId: element.id, handle: handle.name };
                }
            }
        }
        return null;
    }, []);
    // Fonction pour créer un élément selon le mode à une position donnée
    const createElementAtPosition = (0,react.useCallback)((x, y, mode) => {
        const elementId = `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        // ✅ AJOUT: Appliquer le snap à la grille lors de la création d'éléments
        let finalX = x;
        let finalY = y;
        if (state.canvas.snapToGrid && state.canvas.gridSize > 0) {
            const gridSize = state.canvas.gridSize;
            finalX = Math.round(x / gridSize) * gridSize;
            finalY = Math.round(y / gridSize) * gridSize;
        }
        let newElement;
        switch (mode) {
            case 'rectangle':
                newElement = {
                    id: elementId,
                    type: 'rectangle',
                    x: finalX - 50,
                    y: finalY - 50,
                    width: 100,
                    height: 100,
                    fillColor: '#ffffff',
                    strokeColor: '#000000',
                    strokeWidth: 1,
                    borderRadius: 0,
                    rotation: 0,
                    visible: true,
                    locked: false,
                    createdAt: new Date(),
                    updatedAt: new Date()
                };
                break;
            case 'circle':
                newElement = {
                    id: elementId,
                    type: 'circle',
                    x: finalX - 50,
                    y: finalY - 50,
                    width: 100,
                    height: 100,
                    fillColor: '#ffffff',
                    strokeColor: '#000000',
                    strokeWidth: 1,
                    rotation: 0,
                    visible: true,
                    locked: false,
                    createdAt: new Date(),
                    updatedAt: new Date()
                };
                break;
            case 'line':
                newElement = {
                    id: elementId,
                    type: 'line',
                    x: finalX - 50,
                    y: finalY - 1,
                    width: 100,
                    height: 2,
                    strokeColor: '#000000',
                    strokeWidth: 2,
                    rotation: 0,
                    visible: true,
                    locked: false,
                    createdAt: new Date(),
                    updatedAt: new Date()
                };
                break;
            case 'text':
                newElement = {
                    id: elementId,
                    type: 'text',
                    x: finalX - 50,
                    y: finalY - 10,
                    width: 100,
                    height: 30,
                    text: 'Texte',
                    fontSize: 16,
                    color: '#000000',
                    align: 'left',
                    rotation: 0,
                    visible: true,
                    locked: false,
                    createdAt: new Date(),
                    updatedAt: new Date()
                };
                break;
            case 'image':
                newElement = {
                    id: elementId,
                    type: 'image',
                    x: finalX - 50,
                    y: finalY - 50,
                    width: 100,
                    height: 100,
                    src: '',
                    rotation: 0,
                    visible: true,
                    locked: false,
                    createdAt: new Date(),
                    updatedAt: new Date()
                };
                break;
            default:
                return;
        }
        // Ajouter l'élément au state
        dispatch({ type: 'ADD_ELEMENT', payload: newElement });
        // Sélectionner le nouvel élément
        dispatch({ type: 'SET_SELECTION', payload: [elementId] });
        selectedElementRef.current = elementId;
        // Remettre en mode sélection après création
        dispatch({ type: 'SET_MODE', payload: 'select' });
    }, [dispatch, state.canvas.snapToGrid, state.canvas.gridSize]);
    // ✅ Syncer la ref avec l'état Redux (correction: éviter la dépendance sur state entier)
    (0,react.useEffect)(() => {
        selectedElementsRef.current = state.selection.selectedElements;
        // ✅ CORRECTION 5: Garder un snapshot du state courant
        lastKnownStateRef.current = state;
    }, [state.selection.selectedElements, state.elements, state.canvas]); // Dépendances spécifiques au lieu de state entier
    // ✅ CORRECTION 4: Fonction helper pour vérifier que rect est valide
    const validateCanvasRect = (rect) => {
        // Vérifier que rect a des dimensions positives et que left/top sont raisonnables
        if (!rect || rect.width <= 0 || rect.height <= 0) {
            return false;
        }
        // Si rect.left ou rect.top sont très négatifs (canvas hors-écran), c'est OK
        // Mais si ils sont NaN, c'est un problème
        if (isNaN(rect.left) || isNaN(rect.top) || isNaN(rect.right) || isNaN(rect.bottom)) {
            return false;
        }
        return true;
    };
    // Gestionnaire de clic pour la sélection et création d'éléments
    // Fonction utilitaire pour vérifier si un point est dans la hitbox d'un élément (avec marge pour les lignes)
    const isPointInElement = (x, y, element) => {
        // Pour les lignes, ajouter une marge RÉDUITE pour faciliter la sélection sans overlap excessif
        // Pour les autres éléments, pas de marge
        let hitboxMargin = 0;
        if (element.type === 'line') {
            // Marge très réduite: 1-2px max pour les lignes fines
            hitboxMargin = Math.max(1, Math.min(2, element.height * 0.5));
        }
        const left = element.x - hitboxMargin;
        const right = element.x + element.width + hitboxMargin;
        const top = element.y - hitboxMargin;
        const bottom = element.y + element.height + hitboxMargin;
        return x >= left && x <= right && y >= top && y <= bottom;
    };
    const handleCanvasClick = (0,react.useCallback)((event) => {
        const canvas = canvasRef.current;
        if (!canvas)
            return;
        const rect = canvas.getBoundingClientRect();
        // ✅ BUGFIX-008: Validate rect BEFORE using it
        if (!validateCanvasRect(rect)) {
            return;
        }
        // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
        const zoomScale = state.canvas.zoom / 100;
        const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
        const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;
        // ✅ CORRECTION: Vérifier qu'aucun élément n'est cliqué (pour éviter duplication avec handleMouseDown)
        // Note: On cherche du dernier vers le premier pour cohérence avec handleMouseDown
        const clickedElement = [...state.elements].reverse().find(el => isPointInElement(x, y, el));
        // Ne créer un élément que si on clique dans le vide ET qu'on n'est pas en mode sélection
        if (!clickedElement && state.mode !== 'select') {
            createElementAtPosition(x, y, state.mode);
        }
        // Note: La sélection est gérée exclusivement par handleMouseDown
    }, [state, canvasRef, createElementAtPosition]);
    // Gestionnaire de mouse down pour commencer le drag ou resize
    const handleMouseDown = (0,react.useCallback)((event) => {
        const canvas = canvasRef.current;
        if (!canvas) {
            debugLog('[CanvasInteraction] Mouse down ignored - canvas ref null');
            return;
        }
        const rect = canvas.getBoundingClientRect();
        // ✅ CORRECTION 4: Vérifier que rect est valide avant de l'utiliser
        if (!validateCanvasRect(rect)) {
            debugLog('[CanvasInteraction] Mouse down ignored - invalid canvas rect');
            return;
        }
        // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
        const zoomScale = state.canvas.zoom / 100;
        // Calcul des coordonnées du canvas:
        // 1. (event.clientX - rect.left) = position relative au canvas en viewport space
        // 2. - state.canvas.pan.x = appliquer le pan (qui est en canvas space)
        // 3. / zoomScale = appliquer le zoom
        const canvasRelativeX = event.clientX - rect.left;
        const canvasRelativeY = event.clientY - rect.top;
        const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
        const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;
        debugLog(`[CanvasInteraction] Mouse down at canvas coords (${x.toFixed(1)}, ${y.toFixed(1)}), zoom: ${zoomScale}, pan: (${state.canvas.pan.x.toFixed(1)}, ${state.canvas.pan.y.toFixed(1)})`);
        // ✅ Chercher n'importe quel élément au clic (sélectionné ou pas)
        // Note: On cherche du dernier vers le premier pour sélectionner l'élément rendu au-dessus
        const clickedElement = [...state.elements].reverse().find(el => {
            const isIn = isPointInElement(x, y, el);
            return isIn;
        });
        // Si on a cliqué sur un élément
        if (clickedElement) {
            debugLog(`[CanvasInteraction] Clicked element: ${clickedElement.type} (${clickedElement.id})`);
            // ✅ Utiliser state.selection directement (plus fiable que ref)
            const isAlreadySelected = state.selection.selectedElements.includes(clickedElement.id);
            // ✅ Vérifier si la sélection multiple est activée et si Ctrl est enfoncé
            const isMultiSelect = canvasSettings.selectionMultiSelectEnabled && event.ctrlKey;
            if (isMultiSelect) {
                debugLog(`[CanvasInteraction] Multi-select mode - ${isAlreadySelected ? 'removing' : 'adding'} element ${clickedElement.id}`);
                // ✅ Mode sélection multiple
                if (isAlreadySelected) {
                    // Retirer l'élément de la sélection
                    const newSelection = state.selection.selectedElements.filter(id => id !== clickedElement.id);
                    dispatch({ type: 'SET_SELECTION', payload: newSelection });
                }
                else {
                    // Ajouter l'élément à la sélection
                    const newSelection = [...state.selection.selectedElements, clickedElement.id];
                    dispatch({ type: 'SET_SELECTION', payload: newSelection });
                }
                event.preventDefault();
                return;
            }
            else {
                // ✅ Mode sélection simple (comportement actuel)
                if (!isAlreadySelected) {
                    debugLog(`[CanvasInteraction] Selecting element ${clickedElement.id}`);
                    dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
                    // ✅ CORRECTION: Préparer le drag immédiatement pour permettre drag après sélection
                    isDraggingRef.current = true;
                    // Stocker les positions de départ de tous les éléments sélectionnés
                    dragStartRef.current = { [clickedElement.id]: { x: clickedElement.x, y: clickedElement.y } };
                    dragMouseStartRef.current = { x, y }; // Position souris
                    selectedElementRef.current = clickedElement.id;
                    event.preventDefault();
                    return;
                }
                // ✅ L'élément est déjà sélectionné - préparer le drag
                debugLog(`[CanvasInteraction] Starting drag for ${state.selection.selectedElements.length} selected elements`);
                isDraggingRef.current = true;
                // Stocker les positions de départ de tous les éléments sélectionnés
                const startPositions = {};
                state.selection.selectedElements.forEach(id => {
                    const element = state.elements.find(el => el.id === id);
                    if (element) {
                        startPositions[id] = { x: element.x, y: element.y };
                    }
                });
                dragStartRef.current = startPositions;
                dragMouseStartRef.current = { x, y }; // Position souris
                selectedElementRef.current = clickedElement.id;
                event.preventDefault();
                return;
            }
        }
        // Vérifier si on clique sur une poignée de redimensionnement
        const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
        if (resizeHandle) {
            debugLog(`[CanvasInteraction] Starting resize - element: ${resizeHandle.elementId}, handle: ${resizeHandle.handle}`);
            isResizingRef.current = true;
            resizeHandleRef.current = resizeHandle.handle;
            selectedElementRef.current = resizeHandle.elementId;
            resizeMouseStartRef.current = { x, y }; // Position souris au début du resize
            event.preventDefault();
            return;
        }
        // Vérifier si on clique sur une poignée de rotation
        if ((canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.selectionRotationEnabled) && state.selection.selectedElements.length > 0) {
            const selectedElements = state.elements.filter(el => state.selection.selectedElements.includes(el.id));
            if (selectedElements.length > 0) {
                // Calculer les bounds de sélection
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                selectedElements.forEach(el => {
                    minX = Math.min(minX, el.x);
                    minY = Math.min(minY, el.y);
                    maxX = Math.max(maxX, el.x + el.width);
                    maxY = Math.max(maxY, el.y + el.height);
                });
                // Position de la poignée de rotation
                const centerX = (minX + maxX) / 2;
                const rotationHandleY = minY - 20;
                const rotationHandleSize = 8;
                // Vérifier si on est sur la poignée de rotation
                const distance = Math.sqrt((x - centerX) ** 2 + (y - rotationHandleY) ** 2);
                if (distance <= rotationHandleSize / 2) {
                    debugLog(`[CanvasInteraction] Starting rotation for ${state.selection.selectedElements.length} elements`);
                    isRotatingRef.current = true;
                    rotationMouseStartRef.current = { x, y };
                    // Stocker les rotations initiales de tous les éléments sélectionnés
                    const initialRotations = {};
                    state.selection.selectedElements.forEach(elementId => {
                        const element = state.elements.find(el => el.id === elementId);
                        if (element) {
                            initialRotations[elementId] = element.rotation || 0;
                        }
                    });
                    rotationStartRef.current = initialRotations;
                    event.preventDefault();
                    return;
                }
            }
        }
        // ✅ Sinon on a cliqué sur le vide - gérer selon le mode de sélection
        if (selectionMode === 'lasso' || selectionMode === 'rectangle') {
            debugLog(`[CanvasInteraction] Starting ${selectionMode} selection at (${x.toFixed(1)}, ${y.toFixed(1)})`);
            // Commencer une nouvelle sélection
            isSelectingRef.current = true;
            selectionStartRef.current = { x, y };
            selectionPointsRef.current = [{ x, y }];
            if (selectionMode === 'rectangle') {
                selectionRectRef.current = { x, y, width: 0, height: 0 };
            }
            // Démarrer les listeners globaux pour permettre la sélection hors canvas
            startGlobalSelectionListeners();
            // Ne pas désélectionner immédiatement, attendre la fin de la sélection
            event.preventDefault();
            return;
        }
        else {
            // Mode clic simple - désélectionner
            if (state.selection.selectedElements.length > 0) {
                debugLog(`[CanvasInteraction] Clearing selection (${state.selection.selectedElements.length} elements)`);
                dispatch({ type: 'CLEAR_SELECTION' });
                selectedElementRef.current = null;
            }
        }
    }, [state, canvasRef, dispatch, getResizeHandleAtPosition]);
    // Gestionnaire de mouse up pour terminer le drag ou resize
    const handleMouseUp = (0,react.useCallback)(() => {
        debugLog(`[CanvasInteraction] Mouse up - ending interactions (dragging: ${isDraggingRef.current}, resizing: ${isResizingRef.current}, rotating: ${isRotatingRef.current}, selecting: ${isSelectingRef.current})`);
        // Annuler tout RAF en cours et effectuer un dernier update si nécessaire
        if (rafIdRef.current !== null) {
            cancelAnimationFrame(rafIdRef.current);
            rafIdRef.current = null;
            // Effectuer un dernier update si il y en a un en attente
            if (pendingDragUpdateRef.current) {
                performDragUpdate();
            }
            if (pendingRotationUpdateRef.current) {
                performRotationUpdate();
            }
        }
        // ✅ AJOUT: Snap final ultra simple
        const lastState = lastKnownStateRef.current;
        const selectedIds = lastState.selection.selectedElements;
        if (selectedIds.length > 0 && isRotatingRef.current) {
            selectedIds.forEach(elementId => {
                const element = lastState.elements.find(el => el.id === elementId);
                if (element) {
                    let currentRotation = element.rotation || 0;
                    // Normaliser
                    let normalizedRotation = currentRotation % 360;
                    if (normalizedRotation > 180)
                        normalizedRotation -= 360;
                    if (normalizedRotation < -180)
                        normalizedRotation += 360;
                    // Si dans les 10°, forcer à 0°
                    const finalSnapThreshold = 10; // 10 degrees (moins agressif)
                    if (Math.abs(normalizedRotation) <= finalSnapThreshold) {
                        dispatch({
                            type: 'UPDATE_ELEMENT',
                            payload: {
                                id: elementId,
                                updates: { rotation: 0 }
                            }
                        });
                    }
                }
            });
        }
        // Finaliser la sélection lasso/rectangle si en cours
        if (isSelectingRef.current) {
            let selectedElementIds = [];
            if (selectionMode === 'lasso' && selectionPointsRef.current.length > 2) {
                // Sélection lasso : vérifier quels éléments sont à l'intérieur du polygone
                selectedElementIds = state.elements
                    .filter(element => isElementInLasso(element, selectionPointsRef.current))
                    .map(element => element.id);
                debugLog(`[CanvasInteraction] Lasso selection completed - ${selectedElementIds.length} elements selected`);
            }
            else if (selectionMode === 'rectangle' && selectionRectRef.current.width > 0 && selectionRectRef.current.height > 0) {
                // Sélection rectangle : vérifier quels éléments intersectent le rectangle
                selectedElementIds = state.elements
                    .filter(element => isElementInRectangle(element, selectionRectRef.current))
                    .map(element => element.id);
                debugLog(`[CanvasInteraction] Rectangle selection completed - ${selectedElementIds.length} elements selected`);
            }
            // Appliquer la sélection
            if (selectedElementIds.length > 0) {
                debugLog(`[CanvasInteraction] Applying selection: ${selectedElementIds.join(', ')}`);
                dispatch({ type: 'SET_SELECTION', payload: selectedElementIds });
            }
            else {
                debugLog(`[CanvasInteraction] No elements selected - clearing selection`);
                dispatch({ type: 'CLEAR_SELECTION' });
            }
            // Réinitialiser l'état de sélection
            isSelectingRef.current = false;
            selectionPointsRef.current = [];
            selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
        }
        isDraggingRef.current = false;
        isResizingRef.current = false;
        isRotatingRef.current = false;
        resizeHandleRef.current = null;
        selectedElementRef.current = null;
        rotationStartRef.current = {};
        pendingRotationUpdateRef.current = null;
    }, [performDragUpdate, performRotationUpdate, dispatch]);
    // Fonction pour obtenir le curseur de redimensionnement selon la poignée
    const getResizeCursor = (handle) => {
        switch (handle) {
            case 'nw':
            case 'se':
                return 'nw-resize';
            case 'ne':
            case 'sw':
                return 'ne-resize';
            case 'n':
                return 'n-resize';
            case 's':
                return 's-resize';
            case 'w':
                return 'w-resize';
            case 'e':
                return 'e-resize';
            default:
                return 'default';
        }
    };
    // Fonction pour déterminer le curseur approprié selon la position
    const getCursorAtPosition = (0,react.useCallback)((x, y) => {
        // Si on est en train de draguer ou redimensionner, garder le curseur approprié
        if (isDraggingRef.current) {
            return 'grabbing';
        }
        if (isResizingRef.current) {
            return getResizeCursor(resizeHandleRef.current);
        }
        if (isRotatingRef.current) {
            return 'grabbing';
        }
        // Vérifier si on est sur une poignée de rotation
        if ((canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.selectionRotationEnabled) && state.selection.selectedElements.length > 0) {
            const selectedElements = state.elements.filter(el => state.selection.selectedElements.includes(el.id));
            if (selectedElements.length > 0) {
                // Calculer les bounds de sélection
                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                selectedElements.forEach(el => {
                    minX = Math.min(minX, el.x);
                    minY = Math.min(minY, el.y);
                    maxX = Math.max(maxX, el.x + el.width);
                    maxY = Math.max(maxY, el.y + el.height);
                });
                // Position de la poignée de rotation
                const centerX = (minX + maxX) / 2;
                const rotationHandleY = minY - 20;
                const rotationHandleSize = 8;
                // Vérifier si on est sur la poignée de rotation
                const distance = Math.sqrt((x - centerX) ** 2 + (y - rotationHandleY) ** 2);
                if (distance <= rotationHandleSize / 2) {
                    return 'grab';
                }
            }
        }
        // Vérifier si on est sur une poignée de redimensionnement
        const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
        if (resizeHandle) {
            return getResizeCursor(resizeHandle.handle);
        }
        // Vérifier si on est sur un élément sélectionné (pour le déplacement)
        if (state.selection.selectedElements.length > 0) {
            const elementUnderMouse = state.elements.find(el => state.selection.selectedElements.includes(el.id) &&
                isPointInElement(x, y, el));
            if (elementUnderMouse) {
                return 'grab';
            }
        }
        // Curseur par défaut
        return 'default';
    }, [state.selection.selectedElements, state.elements, getResizeHandleAtPosition, canvasSettings.selectionRotationEnabled]);
    // Fonction pour mettre à jour le curseur du canvas
    const updateCursor = (0,react.useCallback)((cursor) => {
        const canvas = canvasRef.current;
        if (canvas && cursor !== currentCursorRef.current) {
            canvas.style.cursor = cursor;
            currentCursorRef.current = cursor;
        }
    }, [canvasRef]);
    // Fonction utilitaire pour calculer le redimensionnement
    const calculateResize = (0,react.useCallback)((element, handle, currentX, currentY, _startPos) => {
        const updates = {};
        const MIN_SIZE = 20;
        switch (handle) {
            case 'se': { // Sud-Est (coin bas-droit) - coin suit directement la souris
                updates.width = Math.max(MIN_SIZE, currentX - element.x);
                updates.height = Math.max(MIN_SIZE, currentY - element.y);
                break;
            }
            case 'sw': { // Sud-Ouest (coin bas-gauche)
                const newX = Math.min(currentX, element.x + element.width - MIN_SIZE);
                updates.width = Math.max(MIN_SIZE, element.x + element.width - newX);
                updates.x = newX;
                updates.height = Math.max(MIN_SIZE, currentY - element.y);
                break;
            }
            case 'ne': { // Nord-Est (coin haut-droit)
                const newY = Math.min(currentY, element.y + element.height - MIN_SIZE);
                updates.width = Math.max(MIN_SIZE, currentX - element.x);
                updates.height = Math.max(MIN_SIZE, element.y + element.height - newY);
                updates.y = newY;
                break;
            }
            case 'nw': { // Nord-Ouest (coin haut-gauche) - coin suit directement la souris
                const newX = Math.min(currentX, element.x + element.width - MIN_SIZE);
                const newY = Math.min(currentY, element.y + element.height - MIN_SIZE);
                updates.width = Math.max(MIN_SIZE, element.x + element.width - newX);
                updates.height = Math.max(MIN_SIZE, element.y + element.height - newY);
                updates.x = newX;
                updates.y = newY;
                break;
            }
            case 'n': { // Nord (haut)
                const newY = Math.min(currentY, element.y + element.height - MIN_SIZE);
                updates.height = Math.max(MIN_SIZE, element.y + element.height - newY);
                updates.y = newY;
                break;
            }
            case 's': { // Sud (bas) - coin suit directement la souris
                updates.height = Math.max(MIN_SIZE, currentY - element.y);
                break;
            }
            case 'w': { // Ouest (gauche)
                const newX = Math.min(currentX, element.x + element.width - MIN_SIZE);
                updates.width = Math.max(MIN_SIZE, element.x + element.width - newX);
                updates.x = newX;
                break;
            }
            case 'e': { // Est (droite) - coin suit directement la souris
                updates.width = Math.max(MIN_SIZE, currentX - element.x);
                break;
            }
        }
        // ✅ AJOUT: Appliquer le snap à la grille pour les positions lors du redimensionnement
        if (state.canvas.snapToGrid && state.canvas.gridSize > 0) {
            const gridSize = state.canvas.gridSize;
            const snapTolerance = 5;
            if (updates.x !== undefined) {
                const nearestGridX = Math.round(updates.x / gridSize) * gridSize;
                if (Math.abs(updates.x - nearestGridX) <= snapTolerance) {
                    updates.x = nearestGridX;
                }
            }
            if (updates.y !== undefined) {
                const nearestGridY = Math.round(updates.y / gridSize) * gridSize;
                if (Math.abs(updates.y - nearestGridY) <= snapTolerance) {
                    updates.y = nearestGridY;
                }
            }
        }
        return updates;
    }, [state.canvas.snapToGrid, state.canvas.gridSize]);
    // Gestionnaire de mouse move pour le drag, resize et curseur
    const handleMouseMove = (0,react.useCallback)((event) => {
        // ✅ CORRECTION 3: Throttling - limiter la fréquence des updates
        const now = Date.now();
        if (now - lastMouseMoveTimeRef.current < MOUSEMOVE_THROTTLE_MS) {
            return; // Skip cet event, trop rapide
        }
        lastMouseMoveTimeRef.current = now;
        const canvas = canvasRef.current;
        if (!canvas)
            return;
        const rect = canvas.getBoundingClientRect();
        // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
        const zoomScale = state.canvas.zoom / 100;
        // Calcul correct des coordonnées avec zoom et pan
        const canvasRelativeX = event.clientX - rect.left;
        const canvasRelativeY = event.clientY - rect.top;
        const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
        const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;
        // Mettre à jour le curseur
        const cursor = getCursorAtPosition(x, y);
        updateCursor(cursor);
        // Gérer la sélection lasso/rectangle en cours
        // Note: Si les listeners globaux sont actifs, la gestion se fait dans globalMouseMoveRef
        if (isSelectingRef.current && !globalMouseMoveRef.current) {
            if (selectionMode === 'lasso') {
                // Ajouter le point actuel au lasso
                selectionPointsRef.current.push({ x, y });
                // Forcer le re-rendu pour afficher le lasso en temps réel
                setSelectionUpdateTrigger(prev => prev + 1);
            }
            else if (selectionMode === 'rectangle') {
                // Mettre à jour le rectangle de sélection
                const startX = Math.min(selectionStartRef.current.x, x);
                const startY = Math.min(selectionStartRef.current.y, y);
                const width = Math.abs(x - selectionStartRef.current.x);
                const height = Math.abs(y - selectionStartRef.current.y);
                selectionRectRef.current = { x: startX, y: startY, width, height };
                // Forcer le re-rendu pour afficher le rectangle en temps réel
                setSelectionUpdateTrigger(prev => prev + 1);
            }
            return;
        }
        if (isDraggingRef.current && selectedElementRef.current) {
            // ✅ OPTIMISATION FLUIDITÉ: Pour le drag multiple, passer directement les coordonnées actuelles de la souris
            // performDragUpdate calculera la nouvelle position pour chaque élément individuellement
            pendingDragUpdateRef.current = { x, y };
            // Programmer l'update avec RAF si pas déjà programmé
            if (rafIdRef.current === null) {
                rafIdRef.current = requestAnimationFrame(performDragUpdate);
            }
        }
        else if (isResizingRef.current && selectedElementRef.current && resizeHandleRef.current) {
            debugLog(`[CanvasInteraction] Resizing element ${selectedElementRef.current} with handle ${resizeHandleRef.current} at (${x.toFixed(1)}, ${y.toFixed(1)})`);
            // ✅ BALANCED: Preserve essential properties without overkill
            const lastState = lastKnownStateRef.current;
            const element = lastState.elements.find(el => el.id === selectedElementRef.current);
            if (!element)
                return;
            const resizeUpdates = calculateResize(element, resizeHandleRef.current, x, y, resizeMouseStartRef.current);
            // ✅ Preserve essential visual properties (corners, styling, etc.)
            const essentialUpdates = { ...resizeUpdates };
            // Keep all properties except the ones we're updating and updatedAt
            const elementAsRecord = element;
            Object.keys(elementAsRecord).forEach(key => {
                if (!(key in resizeUpdates) && key !== 'updatedAt') {
                    essentialUpdates[key] = elementAsRecord[key];
                }
            });
            dispatch({
                type: 'UPDATE_ELEMENT',
                payload: {
                    id: selectedElementRef.current,
                    updates: essentialUpdates
                }
            });
        }
        else if (isRotatingRef.current && state.selection.selectedElements.length > 0) {
            debugLog(`[CanvasInteraction] Rotating ${state.selection.selectedElements.length} elements at mouse position (${x.toFixed(1)}, ${y.toFixed(1)})`);
            // ✅ OPTIMISATION FLUIDITÉ: Pour la rotation, passer les coordonnées actuelles de la souris
            // performRotationUpdate calculera la rotation pour tous les éléments
            pendingRotationUpdateRef.current = { x, y };
            // Programmer l'update avec RAF si pas déjà programmé
            if (rafIdRef.current === null) {
                rafIdRef.current = requestAnimationFrame(performRotationUpdate);
            }
        }
    }, [dispatch, canvasRef, getCursorAtPosition, updateCursor, calculateResize, state.canvas, performDragUpdate]);
    // Cleanup des listeners globaux au démontage du composant
    (0,react.useEffect)(() => {
        return () => {
            stopGlobalSelectionListeners();
        };
    }, [stopGlobalSelectionListeners]);
    // Gestionnaire de clic droit pour afficher le menu contextuel
    const handleContextMenu = (0,react.useCallback)((event, onContextMenu) => {
        event.preventDefault(); // Empêcher le menu contextuel par défaut du navigateur
        const canvas = canvasRef.current;
        if (!canvas)
            return;
        // Pour le menu contextuel, nous utilisons les coordonnées absolues de la souris
        // (pas les coordonnées transformées du canvas)
        const menuX = event.clientX;
        const menuY = event.clientY;
        // Pour la détection d'élément, nous utilisons les coordonnées du canvas
        // Les éléments sont stockés dans l'espace monde (avec pan et zoom)
        // Pour la détection, utilisons les coordonnées dans l'espace canvas
        const rect = canvas.getBoundingClientRect();
        const rawCanvasX = event.clientX - rect.left;
        const rawCanvasY = event.clientY - rect.top;
        // Transformer en coordonnées monde (inverse des transformations du canvas)
        // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
        const zoomScale = state.canvas.zoom / 100;
        const canvasX = (rawCanvasX - state.canvas.pan.x) / zoomScale;
        const canvasY = (rawCanvasY - state.canvas.pan.y) / zoomScale;
        // Trouver l'élément cliqué (avec hitbox adaptée)
        const clickedElement = state.elements.find(el => isPointInElement(canvasX, canvasY, el));
        if (clickedElement) {
            // Ouvrir le menu contextuel pour l'élément
            onContextMenu(menuX, menuY, clickedElement.id);
        }
        else {
            // Ouvrir le menu contextuel général du canvas
            onContextMenu(menuX, menuY);
        }
    }, [state, canvasRef]);
    // Fonctions helper pour la sélection avancée
    const isElementInRectangle = (0,react.useCallback)((element, rect) => {
        // Vérifier si l'élément intersecte ou est contenu dans le rectangle
        const elementRight = element.x + element.width;
        const elementBottom = element.y + element.height;
        const rectRight = rect.x + rect.width;
        const rectBottom = rect.y + rect.height;
        // Vérifier l'intersection
        return !(element.x > rectRight || elementRight < rect.x || element.y > rectBottom || elementBottom < rect.y);
    }, []);
    const isElementInLasso = (0,react.useCallback)((element, points) => {
        if (points.length < 3)
            return false;
        // Utiliser l'algorithme du point dans le polygone (ray casting)
        // Vérifier si le centre de l'élément est dans le lasso
        const centerX = element.x + element.width / 2;
        const centerY = element.y + element.height / 2;
        return isPointInPolygon(centerX, centerY, points);
    }, []);
    const isPointInPolygon = (x, y, polygon) => {
        let inside = false;
        for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
            const xi = polygon[i].x, yi = polygon[i].y;
            const xj = polygon[j].x, yj = polygon[j].y;
            if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
                inside = !inside;
            }
        }
        return inside;
    };
    return {
        handleCanvasClick,
        handleMouseDown,
        handleMouseMove,
        handleMouseUp,
        handleContextMenu,
        // Informations pour le rendu visuel de la sélection
        selectionState: {
            isSelecting: isSelectingRef.current,
            selectionMode,
            selectionRect: selectionRectRef.current,
            selectionPoints: selectionPointsRef.current,
            updateTrigger: selectionUpdateTrigger
        }
    };
};

;// ./assets/js/pdf-builder-react/hooks/useKeyboardShortcuts.ts




/**
 * Hook pour gérer les raccourcis clavier du canvas
 * Implémente les raccourcis configurables via les paramètres canvas
 */
const useKeyboardShortcuts = () => {
    const { state, dispatch } = useBuilder();
    const keyboardShortcutsEnabled = useCanvasSetting('enable_keyboard_shortcuts', true);
    // Références pour éviter les closures stale
    const stateRef = (0,react.useRef)(state);
    const dispatchRef = (0,react.useRef)(dispatch);
    // Mettre à jour les références
    (0,react.useEffect)(() => {
        stateRef.current = state;
        dispatchRef.current = dispatch;
    }, [state, dispatch]);
    /**
     * Gère les événements clavier
     */
    const handleKeyDown = (0,react.useCallback)((event) => {
        const keyboardEvent = event;
        // Ne pas traiter si les raccourcis sont désactivés
        if (!keyboardShortcutsEnabled) {
            debugLog('[KeyboardShortcuts] Shortcuts disabled - ignoring key event');
            return;
        }
        // Ne pas traiter si on est dans un champ de saisie
        const target = event.target;
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.contentEditable === 'true') {
            debugLog('[KeyboardShortcuts] Ignoring key event in input field');
            return;
        }
        const { ctrlKey, metaKey, key, shiftKey } = keyboardEvent;
        const isCtrlOrCmd = ctrlKey || metaKey;
        const shortcut = `${isCtrlOrCmd ? (ctrlKey ? 'Ctrl' : 'Cmd') : ''}${shiftKey ? '+Shift' : ''}+${key.toUpperCase()}`;
        debugLog(`[KeyboardShortcuts] Key pressed: ${shortcut}`);
        switch (key.toLowerCase()) {
            case 'z':
                if (isCtrlOrCmd) {
                    event.preventDefault();
                    if (shiftKey) {
                        // Ctrl+Y ou Cmd+Shift+Z pour redo
                        debugLog('[KeyboardShortcuts] Executing redo (Ctrl+Shift+Z)');
                        dispatchRef.current({ type: 'REDO' });
                    }
                    else {
                        // Ctrl+Z pour undo
                        debugLog('[KeyboardShortcuts] Executing undo (Ctrl+Z)');
                        dispatchRef.current({ type: 'UNDO' });
                    }
                }
                break;
            case 'y':
                if (isCtrlOrCmd && !shiftKey) {
                    event.preventDefault();
                    // Ctrl+Y pour redo
                    debugLog('[KeyboardShortcuts] Executing redo (Ctrl+Y)');
                    dispatchRef.current({ type: 'REDO' });
                }
                break;
            case 'a':
                if (isCtrlOrCmd) {
                    event.preventDefault();
                    // Ctrl+A pour tout sélectionner
                    const allElementIds = stateRef.current.elements.map(el => el.id);
                    debugLog(`[KeyboardShortcuts] Selecting all elements (${allElementIds.length} elements)`);
                    dispatchRef.current({
                        type: 'SET_SELECTION',
                        payload: allElementIds
                    });
                }
                break;
            case 'delete':
            case 'backspace':
                // Supprimer les éléments sélectionnés
                if (stateRef.current.selection.selectedElements.length > 0) {
                    event.preventDefault();
                    debugLog(`[KeyboardShortcuts] Deleting ${stateRef.current.selection.selectedElements.length} selected elements`);
                    // Supprimer tous les éléments sélectionnés
                    stateRef.current.selection.selectedElements.forEach(elementId => {
                        dispatchRef.current({
                            type: 'REMOVE_ELEMENT',
                            payload: elementId
                        });
                    });
                    // Vider la sélection après suppression
                    dispatchRef.current({ type: 'CLEAR_SELECTION' });
                }
                break;
            case 'c':
                if (isCtrlOrCmd) {
                    event.preventDefault();
                    // Ctrl+C pour copier (si des éléments sont sélectionnés)
                    if (stateRef.current.selection.selectedElements.length > 0) {
                        debugLog(`[KeyboardShortcuts] Copying ${stateRef.current.selection.selectedElements.length} selected elements`);
                        // TODO: Implémenter la logique de copie
                    }
                }
                break;
            case 'v':
                if (isCtrlOrCmd) {
                    event.preventDefault();
                    // Ctrl+V pour coller
                    debugLog('[KeyboardShortcuts] Pasting elements');
                    // TODO: Implémenter la logique de collage
                }
                break;
            case 'd':
                if (isCtrlOrCmd) {
                    event.preventDefault();
                    // Ctrl+D pour dupliquer (optionnel)
                    if (stateRef.current.selection.selectedElements.length > 0) {
                        debugLog(`[KeyboardShortcuts] Duplicating ${stateRef.current.selection.selectedElements.length} selected elements`);
                        // TODO: Implémenter la logique de duplication
                    }
                }
                break;
            default:
                // Autres raccourcis peuvent être ajoutés ici
                debugLog(`[KeyboardShortcuts] Unhandled key: ${shortcut}`);
                break;
        }
    }, [keyboardShortcutsEnabled]);
    /**
     * Configure les écouteurs d'événements
     */
    (0,react.useEffect)(() => {
        if (!keyboardShortcutsEnabled) {
            debugLog('[KeyboardShortcuts] Keyboard shortcuts disabled');
            return;
        }
        debugLog('[KeyboardShortcuts] Initializing keyboard shortcuts');
        // Ajouter l'écouteur d'événements
        document.addEventListener('keydown', handleKeyDown);
        // Nettoyer l'écouteur
        return () => {
            debugLog('[KeyboardShortcuts] Cleaning up keyboard shortcuts');
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [handleKeyDown, keyboardShortcutsEnabled]);
    // Retourner des informations sur l'état des raccourcis
    return {
        keyboardShortcutsEnabled,
        hasSelection: state.selection.selectedElements.length > 0,
        canUndo: state.history.past.length > 0,
        canRedo: state.history.future.length > 0,
    };
};

;// ./assets/js/pdf-builder-react/utils/WooCommerceElementsManager.ts
class WooCommerceElementsManager {
    constructor() {
        this.orderData = null;
        this.customerData = null;
    }
    /**
     * Charge les données de commande WooCommerce
     */
    async loadOrderData(orderId) {
        // Simulation d'un appel API WooCommerce
        // En production, ceci ferait un appel à l'API REST WooCommerce
        const response = await this.mockFetchOrderData(orderId);
        this.orderData = response;
        return response;
    }
    /**
     * Charge les données client WooCommerce
     */
    async loadCustomerData(customerId) {
        // Simulation d'un appel API WooCommerce
        const response = await this.mockFetchCustomerData(customerId);
        this.customerData = response;
        return response;
    }
    /**
     * Obtient les données de commande actuelles
     */
    getOrderData() {
        return this.orderData;
    }
    /**
     * Obtient les données client actuelles
     */
    getCustomerData() {
        return this.customerData;
    }
    /**
     * Obtient le numéro de commande formaté
     */
    getOrderNumber() {
        if (!this.orderData)
            return 'CMD-XXXX-XXXX';
        return this.orderData.order_number || `CMD-${this.orderData.id}`;
    }
    /**
     * Obtient les informations client formatées
     */
    getCustomerInfo() {
        var _a, _b;
        if (!this.customerData && !this.orderData) {
            return {
                name: 'Client Inconnu',
                address: 'Adresse non disponible',
                email: 'email@inconnu.com',
                phone: '+33 0 00 00 00 00'
            };
        }
        const billing = ((_a = this.customerData) === null || _a === void 0 ? void 0 : _a.billing) || ((_b = this.orderData) === null || _b === void 0 ? void 0 : _b.billing);
        if (!billing) {
            return {
                name: 'Client Inconnu',
                address: 'Adresse non disponible',
                email: 'email@inconnu.com',
                phone: '+33 0 00 00 00 00'
            };
        }
        const fullName = `${billing.first_name} ${billing.last_name}`.trim();
        const address = [
            billing.address_1,
            billing.address_2,
            `${billing.postcode} ${billing.city}`,
            billing.country
        ].filter(Boolean).join(', ');
        return {
            name: fullName || 'Client Inconnu',
            address: address || 'Adresse non disponible',
            email: billing.email || 'email@inconnu.com',
            phone: billing.phone || '+33 0 00 00 00 00'
        };
    }
    /**
     * Obtient les articles de commande formatés pour l'affichage
     */
    getOrderItems() {
        if (!this.orderData) {
            return [];
        }
        return this.orderData.line_items.map(item => ({
            sku: item.sku || `SKU-${item.product_id}`,
            name: item.name,
            description: this.getProductDescription(item),
            qty: item.quantity,
            price: parseFloat(item.subtotal) / item.quantity,
            discount: this.calculateItemDiscount(item),
            total: parseFloat(item.total)
        }));
    }
    /**
     * Calcule les totaux de commande
     */
    getOrderTotals() {
        if (!this.orderData) {
            return {
                subtotal: 0,
                discount: 0,
                shipping: 0,
                tax: 0,
                total: 0,
                currency: 'EUR'
            };
        }
        const subtotal = parseFloat(this.orderData.subtotal);
        const discount = parseFloat(this.orderData.discount_total);
        const shipping = parseFloat(this.orderData.shipping_total);
        const tax = parseFloat(this.orderData.total_tax);
        const total = parseFloat(this.orderData.total);
        return {
            subtotal,
            discount,
            shipping,
            tax,
            total,
            currency: this.orderData.currency
        };
    }
    /**
     * Obtient la date de commande formatée
     */
    getOrderDate() {
        if (!this.orderData)
            return new Date().toLocaleDateString('fr-FR');
        return new Date(this.orderData.date_created).toLocaleDateString('fr-FR');
    }
    /**
     * Réinitialise les données
     */
    reset() {
        this.orderData = null;
        this.customerData = null;
    }
    // Méthodes privées
    getProductDescription(item) {
        // Recherche dans les meta_data pour une description
        const descriptionMeta = item.meta_data.find(meta => meta.key === '_description');
        return (descriptionMeta === null || descriptionMeta === void 0 ? void 0 : descriptionMeta.value) || 'Description non disponible';
    }
    calculateItemDiscount(item) {
        const subtotal = parseFloat(item.subtotal);
        const total = parseFloat(item.total);
        return Math.max(0, subtotal - total);
    }
    // Méthodes de simulation (à remplacer par de vrais appels API)
    async mockFetchOrderData(orderId) {
        // Simulation d'un délai réseau
        await new Promise(resolve => setTimeout(resolve, 100));
        // Données fictives réalistes
        return {
            id: parseInt(orderId),
            order_number: `CMD-2024-${orderId.padStart(4, '0')}`,
            status: 'completed',
            currency: 'EUR',
            date_created: new Date().toISOString(),
            date_modified: new Date().toISOString(),
            total: '279.96',
            subtotal: '259.96',
            total_tax: '20.00',
            shipping_total: '8.50',
            discount_total: '15.00',
            customer_id: 123,
            billing: {
                first_name: 'Marie',
                last_name: 'Dupont',
                company: '',
                address_1: '15 rue des Lilas',
                address_2: '',
                city: 'Paris',
                state: '',
                postcode: '75001',
                country: 'FR',
                email: 'marie.dupont@email.com',
                phone: '+33 6 12 34 56 78'
            },
            shipping: {
                first_name: 'Marie',
                last_name: 'Dupont',
                company: '',
                address_1: '15 rue des Lilas',
                address_2: '',
                city: 'Paris',
                state: '',
                postcode: '75001',
                country: 'FR'
            },
            line_items: [
                {
                    id: 1,
                    name: 'T-shirt Premium Bio',
                    product_id: 123,
                    variation_id: 0,
                    quantity: 2,
                    tax_class: '',
                    subtotal: '59.98',
                    subtotal_tax: '11.996',
                    total: '59.98',
                    total_tax: '11.996',
                    taxes: [{ id: 1, total: '11.996', subtotal: '11.996' }],
                    meta_data: [],
                    sku: 'TSHIRT-001',
                    price: 29.99
                },
                {
                    id: 2,
                    name: 'Jean Slim Fit Noir',
                    product_id: 456,
                    variation_id: 0,
                    quantity: 1,
                    tax_class: '',
                    subtotal: '89.99',
                    subtotal_tax: '17.998',
                    total: '79.99',
                    total_tax: '15.998',
                    taxes: [{ id: 1, total: '15.998', subtotal: '17.998' }],
                    meta_data: [],
                    sku: 'JEAN-045',
                    price: 89.99
                }
            ],
            shipping_lines: [{
                    id: 1,
                    method_title: 'Livraison Standard',
                    method_id: 'flat_rate',
                    total: '8.50',
                    total_tax: '0.00',
                    taxes: []
                }],
            tax_lines: [{
                    id: 1,
                    rate_code: 'FR-TVA-20',
                    rate_id: 1,
                    label: 'TVA (20%)',
                    compound: false,
                    tax_total: '20.00',
                    shipping_tax_total: '0.00',
                    rate_percent: 20
                }],
            coupon_lines: [{
                    id: 1,
                    code: 'ETE2024',
                    discount: '15.00',
                    discount_tax: '0.00'
                }]
        };
    }
    async mockFetchCustomerData(customerId) {
        // Simulation d'un délai réseau
        await new Promise(resolve => setTimeout(resolve, 50));
        return {
            id: customerId,
            date_created: new Date().toISOString(),
            date_modified: new Date().toISOString(),
            email: 'marie.dupont@email.com',
            first_name: 'Marie',
            last_name: 'Dupont',
            role: 'customer',
            username: 'marie_dupont',
            billing: {
                first_name: 'Marie',
                last_name: 'Dupont',
                company: '',
                address_1: '15 rue des Lilas',
                address_2: '',
                city: 'Paris',
                state: '',
                postcode: '75001',
                country: 'FR',
                email: 'marie.dupont@email.com',
                phone: '+33 6 12 34 56 78'
            },
            shipping: {
                first_name: 'Marie',
                last_name: 'Dupont',
                company: '',
                address_1: '15 rue des Lilas',
                address_2: '',
                city: 'Paris',
                state: '',
                postcode: '75001',
                country: 'FR'
            }
        };
    }
}
// Instance singleton
const wooCommerceManager = new WooCommerceElementsManager();

;// ./assets/js/pdf-builder-react/utils/ElementChangeTracker.ts
/**
 * 🔍 REAL-TIME ELEMENT & PROPERTY CHANGE TRACKER
 *
 * Tracks all element changes, property modifications, and canvas updates
 * in real-time with detailed logging and diff detection.
 */
class ElementChangeTracker {
    constructor() {
        this.previousSnapshots = new Map();
        this.changeHistory = [];
        this.maxHistorySize = 500;
        this.listeners = [];
        this.debugEnabled = false; // Set to true to enable logging
    }
    /**
     * Track element state at a specific point in time
     */
    trackElements(elements) {
        const currentSnapshots = new Map();
        const changes = [];
        elements.forEach((element) => {
            const snapshot = {
                ...element,
                timestamp: Date.now()
            };
            currentSnapshots.set(element.id, snapshot);
            const previousSnapshot = this.previousSnapshots.get(element.id);
            if (!previousSnapshot) {
                // Element created
                changes.push({
                    elementId: element.id,
                    property: '__created__',
                    oldValue: undefined,
                    newValue: snapshot,
                    timestamp: snapshot.timestamp,
                    changeType: 'created'
                });
            }
            else {
                // Check for property changes
                this.detectPropertyChanges(previousSnapshot, snapshot, changes);
            }
        });
        // Check for deleted elements
        this.previousSnapshots.forEach((snapshot, elementId) => {
            if (!currentSnapshots.has(elementId)) {
                changes.push({
                    elementId,
                    property: '__deleted__',
                    oldValue: snapshot,
                    newValue: undefined,
                    timestamp: Date.now(),
                    changeType: 'deleted'
                });
            }
        });
        this.previousSnapshots = currentSnapshots;
        this.addChangesToHistory(changes);
        this.notifyListeners(changes);
        return changes;
    }
    /**
     * Detect all property changes between two snapshots
     */
    detectPropertyChanges(previous, current, changes) {
        const allKeys = new Set([
            ...Object.keys(previous),
            ...Object.keys(current)
        ]);
        allKeys.forEach(key => {
            if (key === 'timestamp')
                return;
            const oldValue = previous[key];
            const newValue = current[key];
            // Deep comparison for objects/arrays
            if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
                changes.push({
                    elementId: previous.id,
                    property: key,
                    oldValue,
                    newValue,
                    timestamp: current.timestamp,
                    changeType: 'property_changed'
                });
            }
        });
    }
    /**
     * Get emoji for different property types
     */
    getPropertyEmoji(property) {
        const emojiMap = {
            x: '📍',
            y: '📍',
            width: '📏',
            height: '📏',
            rotation: '🔄',
            opacity: '👁️',
            visible: '👁️',
            locked: '🔒',
            color: '🎨',
            fillColor: '🎨',
            strokeColor: '🖌️',
            fontSize: '📝',
            fontFamily: '📝',
            text: '📄',
            src: '🖼️',
            url: '🔗',
            backgroundColor: '🎨',
            textColor: '🎨',
            borderColor: '🖌️',
            borderRadius: '🔲',
            showHeaders: '📊',
            showBorders: '📊',
            showAlternatingRows: '📊'
        };
        return emojiMap[property] || '🔧';
    }
    /**
     * Determine the type of property
     */
    getPropertyType(value) {
        if (value === null)
            return 'null';
        if (Array.isArray(value))
            return `array[${value.length}]`;
        if (typeof value === 'object')
            return `object{${Object.keys(value).length}}`;
        return typeof value;
    }
    /**
     * Format value for logging
     */
    formatValue(value, maxLength = 50) {
        if (value === undefined)
            return 'undefined';
        if (value === null)
            return 'null';
        if (typeof value === 'boolean')
            return value ? '✓ true' : '✗ false';
        if (typeof value === 'number')
            return value.toFixed(2);
        if (typeof value === 'string') {
            const truncated = value.length > maxLength ? value.slice(0, maxLength) + '...' : value;
            return `"${truncated}"`;
        }
        if (Array.isArray(value))
            return `[${value.length} items]`;
        if (typeof value === 'object')
            return `{${Object.keys(value).length} props}`;
        return String(value);
    }
    /**
     * Add changes to history with size limit
     */
    addChangesToHistory(changes) {
        this.changeHistory.push(...changes);
        if (this.changeHistory.length > this.maxHistorySize) {
            this.changeHistory = this.changeHistory.slice(-this.maxHistorySize);
        }
    }
    /**
     * Notify all registered listeners
     */
    notifyListeners(changes) {
        changes.forEach(change => {
            this.listeners.forEach(listener => listener(change));
        });
    }
    /**
     * Subscribe to changes
     */
    onChange(callback) {
        this.listeners.push(callback);
        return () => {
            this.listeners = this.listeners.filter(l => l !== callback);
        };
    }
    /**
     * Get change history
     */
    getHistory() {
        return [...this.changeHistory];
    }
    /**
     * Get changes for a specific element
     */
    getElementHistory(elementId) {
        return this.changeHistory.filter(c => c.elementId === elementId);
    }
    /**
     * Get all changes for a specific property
     */
    getPropertyHistory(property) {
        return this.changeHistory.filter(c => c.property === property || c.changeType === 'created');
    }
    /**
     * Get changes within a time range
     */
    getChangesBetween(startTime, endTime) {
        return this.changeHistory.filter(c => c.timestamp >= startTime && c.timestamp <= endTime);
    }
    /**
     * Reset history (but keep current snapshots)
     */
    clearHistory() {
        this.changeHistory = [];
    }
    /**
     * Get current snapshots
     */
    getSnapshots() {
        return new Map(this.previousSnapshots);
    }
    /**
     * Generate a detailed report
     */
    generateReport() {
        const totalChanges = this.changeHistory.length;
        const elementsTracked = this.previousSnapshots.size;
        const changeSummary = this.changeHistory.reduce((acc, change) => {
            acc[change.changeType] = (acc[change.changeType] || 0) + 1;
            return acc;
        }, {});
        return `
📊 ELEMENT TRACKER REPORT
========================
Total Changes: ${totalChanges}
Elements Tracked: ${elementsTracked}
Change Breakdown:
  - Created: ${changeSummary.created || 0}
  - Updated: ${changeSummary.updated || 0}
  - Deleted: ${changeSummary.deleted || 0}
  - Property Changes: ${changeSummary.property_changed || 0}
========================
    `.trim();
    }
}
// Export singleton instance
const elementChangeTracker = new ElementChangeTracker();

;// ./assets/js/pdf-builder-react/utils/CanvasMonitoringDashboard.ts
/**
 * 🎯 CANVAS MONITORING DASHBOARD
 *
 * Provides real-time monitoring, visualization, and debugging tools
 * for element changes, renders, and performance metrics.
 */

class CanvasMonitoringDashboard {
    /**
     * Initialize and start monitoring
     */
    static initialize() {
        this.monitoringEnabled = true;
        this.startTime = Date.now();
        // Silent initialization - dashboard is available via showDashboard()
    }
    /**
     * Increment render counter
     */
    static recordRender() {
        if (this.monitoringEnabled) {
            this.renderCount++;
        }
    }
    /**
     * Record element changes
     */
    static recordChanges(count) {
        if (this.monitoringEnabled) {
            this.elementChanges += count;
        }
    }
    /**
     * Get formatted history
     */
    static getHistory() {
        return elementChangeTracker.getHistory();
    }
    /**
     * Get history for specific element
     */
    static getElementHistory(elementId) {
        return elementChangeTracker.getElementHistory(elementId);
    }
    /**
     * Get history for specific property
     */
    static getPropertyHistory(property) {
        return elementChangeTracker.getPropertyHistory(property);
    }
    /**
     * Generate detailed report
     */
    static generateReport() {
        const history = elementChangeTracker.getHistory();
        const snapshots = elementChangeTracker.getSnapshots();
        let report = '\n📋 DETAILED MONITORING REPORT\n';
        report += '═══════════════════════════════════════\n';
        report += `Total Events: ${history.length}\n`;
        report += `Elements: ${snapshots.size}\n\n`;
        // Recent changes
        report += 'Recent Changes (last 10):\n';
        history.slice(-10).forEach(change => {
            const time = new Date(change.timestamp).toLocaleTimeString();
            if (change.changeType === 'created') {
                report += `  [${time}] ✨ Created: ${change.elementId}\n`;
            }
            else if (change.changeType === 'deleted') {
                report += `  [${time}] 🗑️ Deleted: ${change.elementId}\n`;
            }
            else {
                report += `  [${time}] 🔧 ${change.elementId}.${change.property}: ${change.oldValue} → ${change.newValue}\n`;
            }
        });
        return report;
    }
    /**
     * Get emoji for change type
     */
    static getChangeTypeEmoji(type) {
        const emojiMap = {
            created: '✨',
            deleted: '🗑️',
            updated: '🔄',
            property_changed: '🔧'
        };
        return emojiMap[type] || '❓';
    }
    /**
     * Get emoji for property
     */
    static getPropertyEmoji(prop) {
        const emojiMap = {
            x: '📍',
            y: '📍',
            width: '📏',
            height: '📏',
            rotation: '🔄',
            opacity: '👁️',
            visible: '👁️',
            locked: '🔒',
            color: '🎨',
            fillColor: '🎨',
            strokeColor: '🖌️',
            fontSize: '📝',
            fontFamily: '📝',
            text: '📄'
        };
        return emojiMap[prop] || '🔧';
    }
    /**
     * Export history as JSON
     */
    static exportHistory() {
        return JSON.stringify({
            timestamp: new Date().toISOString(),
            history: elementChangeTracker.getHistory(),
            snapshots: Array.from(elementChangeTracker.getSnapshots().values())
        }, null, 2);
    }
    /**
     * Clear all history
     */
    static clearHistory() {
        elementChangeTracker.clearHistory();
        this.renderCount = 0;
        this.elementChanges = 0;
    }
}
CanvasMonitoringDashboard.monitoringEnabled = false;
CanvasMonitoringDashboard.startTime = 0;
CanvasMonitoringDashboard.renderCount = 0;
CanvasMonitoringDashboard.elementChanges = 0;
if (typeof window !== 'undefined') {
    window.CanvasMonitoringDashboard = CanvasMonitoringDashboard;
}

// EXTERNAL MODULE: ./node_modules/react-dom/index.js
var react_dom = __webpack_require__(961);
;// ./assets/js/pdf-builder-react/components/ui/ContextMenu.tsx




const ContextMenu = ({ items, position, onClose, isVisible }) => {
    const menuRef = (0,react.useRef)(null);
    const [hoveredItem, setHoveredItem] = (0,react.useState)(null);
    const [openSubmenu, setOpenSubmenu] = (0,react.useState)(null);
    const [submenuPosition, setSubmenuPosition] = (0,react.useState)(null);
    const closeTimeoutRef = (0,react.useRef)(null);
    // Calculer la position corrigée pour garder le menu à l'écran
    const adjustedPosition = (0,react.useMemo)(() => {
        let adjustedX = position.x;
        let adjustedY = position.y;
        if (typeof window !== 'undefined') {
            // Largeur et hauteur estimées du menu compact
            const menuWidth = 160; // Max width du menu compact
            // Calculer la hauteur réelle en fonction du type d'éléments
            let menuHeight = 0;
            items.forEach(item => {
                if (item.section) {
                    menuHeight += 14; // Hauteur des sections avec padding
                }
                else if (item.separator) {
                    menuHeight += 3; // Hauteur des séparateurs avec margin
                }
                else {
                    menuHeight += 18; // Hauteur des éléments normaux
                }
            });
            menuHeight += 2; // Padding vertical du menu
            // Vérifier si le menu sort à droite
            if (adjustedX + menuWidth > window.innerWidth) {
                adjustedX = window.innerWidth - menuWidth - 10;
            }
            // Vérifier si le menu sort en bas
            if (adjustedY + menuHeight > window.innerHeight) {
                adjustedY = window.innerHeight - menuHeight - 10;
            }
            // Vérifier si le menu sort en haut (après ajustement vers le bas)
            if (adjustedY < 0) {
                adjustedY = 10; // Positionner en haut avec une marge
            }
            // Vérifier les limites à gauche
            if (adjustedX < 0)
                adjustedX = 10;
        }
        return { x: adjustedX, y: adjustedY };
    }, [position, items]);
    // Nettoyer les timeouts quand le composant se démonte
    (0,react.useEffect)(() => {
        return () => {
            if (closeTimeoutRef.current) {
                clearTimeout(closeTimeoutRef.current);
            }
        };
    }, []);
    // Calculer la position du sous-menu quand il s'ouvre
    (0,react.useLayoutEffect)(() => {
        if (openSubmenu && menuRef.current) {
            const menuElement = menuRef.current;
            const parentItem = menuElement.querySelector(`[data-item-id="${openSubmenu}"]`);
            if (parentItem) {
                const menuRect = menuElement.getBoundingClientRect();
                const parentRect = parentItem.getBoundingClientRect();
                let submenuX = menuRect.right - 2; // Positionner à droite du menu parent
                let submenuY = parentRect.top; // Aligner avec l'élément parent
                // Vérifier si le sous-menu sort à droite de l'écran
                const submenuWidth = 160;
                if (submenuX + submenuWidth > window.innerWidth) {
                    submenuX = menuRect.left - submenuWidth + 2; // Positionner à gauche
                }
                // Vérifier si le sous-menu sort en bas de l'écran
                const submenuHeight = 200; // Estimation
                if (submenuY + submenuHeight > window.innerHeight) {
                    submenuY = window.innerHeight - submenuHeight - 10;
                }
                // Vérifier si le sous-menu sort en haut
                if (submenuY < 0) {
                    submenuY = 10;
                }
                setSubmenuPosition(_prev => ({ x: submenuX, y: submenuY }));
            }
        }
        else {
            setSubmenuPosition(null);
        }
    }, [openSubmenu]);
    (0,react.useEffect)(() => {
        if (!isVisible)
            return;
        const handleClickOutside = (event) => {
            // Petite attente pour permettre au menu de s'ouvrir d'abord
            setTimeout(() => {
                if (!menuRef.current)
                    return;
                const mouseEvent = event;
                // Ne pas fermer si c'est un clic droit (pour éviter de fermer immédiatement)
                if (mouseEvent.button === 2)
                    return;
                // Vérifier si le clic est en dehors du menu
                if (!menuRef.current.contains(event.target)) {
                    onClose();
                }
            }, 10);
        };
        const handleContextMenu = (event) => {
            // Empêcher l'ouverture d'un nouveau menu contextuel sur le menu existant
            if (menuRef.current && menuRef.current.contains(event.target)) {
                event.preventDefault();
            }
        };
        const handleEscape = (event) => {
            const keyboardEvent = event;
            if (keyboardEvent.key === 'Escape') {
                onClose();
            }
        };
        // Délai minimal pour permettre au menu de se rendre
        const timer = setTimeout(() => {
            document.addEventListener('mousedown', handleClickOutside, { passive: true });
            document.addEventListener('contextmenu', handleContextMenu, { passive: true });
            document.addEventListener('keydown', handleEscape, { passive: true });
        }, 50);
        return () => {
            clearTimeout(timer);
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('contextmenu', handleContextMenu);
            document.removeEventListener('keydown', handleEscape);
        };
    }, [isVisible, onClose]);
    if (!isVisible) {
        return null;
    }
    const handleItemClick = (item) => {
        if (!item.disabled && !item.separator && item.action) {
            item.action();
            onClose();
        }
    };
    const menuElement = ((0,jsx_runtime.jsx)("div", { ref: menuRef, className: "context-menu", onMouseLeave: () => {
            // Délai plus long pour permettre la navigation vers les sous-menus
            if (closeTimeoutRef.current) {
                clearTimeout(closeTimeoutRef.current);
            }
            closeTimeoutRef.current = window.setTimeout(() => {
                setOpenSubmenu(null);
                setHoveredItem(null);
            }, 300);
        }, onMouseEnter: () => {
            // Annuler la fermeture si on revient dans le menu
            if (closeTimeoutRef.current) {
                clearTimeout(closeTimeoutRef.current);
                closeTimeoutRef.current = null;
            }
        }, style: {
            position: 'fixed',
            left: `${adjustedPosition.x}px`,
            top: `${adjustedPosition.y}px`,
            opacity: 1,
            visibility: 'visible',
            pointerEvents: 'auto',
            zIndex: 999999,
            background: 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
            border: '1px solid #e2e8f0',
            WebkitBorderRadius: '8px',
            MozBorderRadius: '8px',
            borderRadius: '8px',
            WebkitBoxShadow: '0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04)',
            MozBoxShadow: '0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04)',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04)',
            minWidth: '120px',
            maxWidth: '160px',
            padding: '1px 0',
            WebkitTransition: 'opacity 0.15s ease-in-out',
            MozTransition: 'opacity 0.15s ease-in-out',
            OTransition: 'opacity 0.15s ease-in-out',
            transition: 'opacity 0.15s ease-in-out',
            WebkitTransformOrigin: 'top left',
            MozTransformOrigin: 'top left',
            msTransformOrigin: 'top left',
            OTransformOrigin: 'top left',
            transformOrigin: 'top left',
        }, children: items.map((item) => ((0,jsx_runtime.jsx)("div", { children: item.section ? ((0,jsx_runtime.jsx)("div", { className: "context-menu-section", style: { padding: '2px 6px 1px' }, children: (0,jsx_runtime.jsx)("div", { className: "context-menu-section-title", style: { fontSize: '8px', fontWeight: '600', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.5px', padding: '0px', marginBottom: '0px' }, children: item.section }) })) : item.separator ? ((0,jsx_runtime.jsx)("div", { className: "context-menu-separator", style: { height: '1px', background: 'linear-gradient(90deg, transparent 0%, #e2e8f0 20%, #e2e8f0 80%, transparent 100%)', margin: '1px 0', border: 'none' } })) : ((0,jsx_runtime.jsxs)("div", { className: `context-menu-item ${item.disabled ? 'disabled' : ''}`, "data-item-id": item.id, onClick: () => handleItemClick(item), onMouseEnter: () => {
                    setHoveredItem(item.id);
                    if (item.children && item.children.length > 0) {
                        setOpenSubmenu(item.id);
                    }
                    else {
                        setOpenSubmenu(null);
                    }
                }, style: {
                    display: 'flex',
                    alignItems: 'center',
                    padding: '1px 4px',
                    cursor: item.disabled ? 'not-allowed' : 'pointer',
                    userSelect: 'none',
                    transition: 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)',
                    position: 'relative',
                    minHeight: '16px',
                    border: 'none',
                    background: hoveredItem === item.id ? '#f1f5f9' : 'transparent',
                    color: item.disabled ? '#94a3b8' : '#334155',
                    fontSize: '11px',
                    fontWeight: '500',
                }, children: [item.icon && (0,jsx_runtime.jsx)("span", { className: "context-menu-item-icon", style: { width: '10px', height: '10px', marginRight: '3px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: item.disabled ? '#94a3b8' : '#64748b', fontSize: '9px' }, children: item.icon }), item.label && (0,jsx_runtime.jsx)("span", { className: "context-menu-item-text", style: { flex: '1', fontSize: '10px', fontWeight: '500', color: item.disabled ? '#94a3b8' : '#334155' }, children: item.label }), item.shortcut && (0,jsx_runtime.jsx)("span", { className: "context-menu-item-shortcut", style: { fontSize: '8px', fontWeight: '500', color: item.disabled ? '#94a3b8' : '#64748b', background: 'rgba(148, 163, 184, 0.1)', padding: '0px 1px', borderRadius: '1px', marginLeft: '3px' }, children: item.shortcut }), item.children && item.children.length > 0 && ((0,jsx_runtime.jsx)("span", { className: "context-menu-submenu-arrow", style: { marginLeft: '4px', color: '#64748b', fontSize: '8px' }, children: "\u25B6" }))] })) }, item.id))) }));
    // Rendre les sous-menus ouverts
    const renderSubmenus = () => {
        if (!openSubmenu || !submenuPosition)
            return null;
        const parentItem = items.find(item => item.id === openSubmenu);
        if (!parentItem || !parentItem.children)
            return null;
        return ((0,jsx_runtime.jsx)("div", { onMouseEnter: () => {
                // Annuler la fermeture si on entre dans le sous-menu
                if (closeTimeoutRef.current) {
                    clearTimeout(closeTimeoutRef.current);
                    closeTimeoutRef.current = null;
                }
            }, onMouseLeave: () => {
                // Délai pour permettre le retour au menu principal
                if (closeTimeoutRef.current) {
                    clearTimeout(closeTimeoutRef.current);
                }
                closeTimeoutRef.current = window.setTimeout(() => {
                    setOpenSubmenu(null);
                    setHoveredItem(null);
                }, 300);
            }, children: (0,jsx_runtime.jsx)(ContextMenu, { items: parentItem.children, position: submenuPosition, onClose: () => setOpenSubmenu(null), isVisible: true }) }));
    };
    // Utiliser un Portal pour rendre le menu au niveau du document body
    return (0,react_dom.createPortal)((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [menuElement, renderSubmenus()] }), document.body);
};

;// ./assets/js/pdf-builder-react/components/canvas/Canvas.tsx










// Fonctions utilitaires pour la gestion mémoire des images
const estimateImageMemorySize = (img) => {
    // Estimation basée sur les dimensions et le nombre de canaux (RGBA = 4 octets par pixel)
    const bytesPerPixel = 4;
    return img.naturalWidth * img.naturalHeight * bytesPerPixel;
};
const cleanupImageCache = (imageCache) => {
    const cache = imageCache.current;
    if (cache.size <= 100)
        return; // Max 100 images
    // Trier par date d'utilisation et supprimer les plus anciennes
    const entries = Array.from(cache.entries()).sort(([, a], [, b]) => a.lastUsed - b.lastUsed);
    const toRemove = entries.slice(0, Math.ceil(cache.size * 0.2)); // Supprimer 20%
    toRemove.forEach(([url]) => cache.delete(url));
};


// Fonctions utilitaires de dessin (déplacées en dehors du composant pour éviter les avertissements React Compiler)
// Fonction helper pour normaliser les couleurs
const normalizeColor = (color) => {
    if (!color || color === 'transparent') {
        return 'rgba(0,0,0,0)'; // Transparent
    }
    return color;
};
// Fonction utilitaire pour rectangle arrondi
const roundedRect = (ctx, x, y, width, height, radius) => {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
};
// Fonctions de dessin pour les éléments
const drawRectangle = (ctx, element) => {
    const props = element;
    const fillColor = normalizeColor(props.fillColor || '#ffffff');
    const strokeColor = normalizeColor(props.strokeColor || '#000000');
    const strokeWidth = props.strokeWidth || 1;
    const borderRadius = props.borderRadius || 0;
    ctx.fillStyle = fillColor;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;
    if (borderRadius > 0) {
        roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
        ctx.fill();
        ctx.stroke();
    }
    else {
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
    }
};
const drawCircle = (ctx, element) => {
    const props = element;
    const fillColor = normalizeColor(props.fillColor || '#ffffff');
    const strokeColor = normalizeColor(props.strokeColor || '#000000');
    const strokeWidth = props.strokeWidth || 1;
    const centerX = element.width / 2;
    const centerY = element.height / 2;
    const radius = Math.min(centerX, centerY);
    ctx.fillStyle = fillColor;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.fill();
    ctx.stroke();
};
const drawText = (ctx, element) => {
    const props = element;
    const text = props.text || 'Text';
    const fontSize = props.fontSize || 16;
    const color = normalizeColor(props.color || '#000000');
    const align = props.align || 'left';
    ctx.fillStyle = color;
    ctx.font = `${fontSize}px Arial`;
    ctx.textAlign = align;
    const x = align === 'center' ? element.width / 2 : align === 'right' ? element.width : 0;
    ctx.fillText(text, x, fontSize);
};
const drawLine = (ctx, element) => {
    const props = element;
    const strokeColor = normalizeColor(props.strokeColor || '#000000');
    const strokeWidth = props.strokeWidth || 2;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;
    ctx.beginPath();
    ctx.moveTo(0, element.height / 2); // Centre verticalement
    ctx.lineTo(element.width, element.height / 2); // Ligne horizontale droite
    ctx.stroke();
};
// Fonction pour dessiner une image
const drawImage = (ctx, element, imageCache) => {
    const props = element;
    const imageUrl = props.src || '';
    if (!imageUrl) {
        // Pas d'URL, dessiner un placeholder
        ctx.fillStyle = '#f0f0f0';
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeStyle = '#cccccc';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
        ctx.fillStyle = '#999999';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Image', element.width / 2, element.height / 2);
        return;
    }
    // Vérifier si l'image est en cache
    let cachedImage = imageCache.current.get(imageUrl);
    if (!cachedImage) {
        // Créer une nouvelle image et la mettre en cache
        const img = document.createElement('img');
        img.crossOrigin = 'anonymous';
        img.src = imageUrl;
        // Attendre que l'image soit chargée pour calculer sa taille mémoire
        img.onload = () => {
            const size = estimateImageMemorySize(img);
            imageCache.current.set(imageUrl, {
                image: img,
                size: size,
                lastUsed: Date.now()
            });
            // Déclencher un nettoyage après ajout
            cleanupImageCache(imageCache);
        };
        img.onerror = () => {
            debugWarn(`[Canvas] Failed to load image: ${imageUrl}`);
        };
        // Retourner temporairement pour éviter les erreurs
        return;
    }
    const img = cachedImage.image;
    // Mettre à jour la date d'utilisation
    cachedImage.lastUsed = Date.now();
    // Si l'image est chargée, la dessiner
    if (img.complete && img.naturalHeight !== 0) {
        // Appliquer object-fit
        const objectFit = props.objectFit || 'cover';
        let drawX = 0, drawY = 0, drawWidth = element.width, drawHeight = element.height;
        let sourceX = 0, sourceY = 0, sourceWidth = img.naturalWidth, sourceHeight = img.naturalHeight;
        if (objectFit === 'contain') {
            const ratio = Math.min(element.width / img.naturalWidth, element.height / img.naturalHeight);
            drawWidth = img.naturalWidth * ratio;
            drawHeight = img.naturalHeight * ratio;
            drawX = (element.width - drawWidth) / 2;
            drawY = (element.height - drawHeight) / 2;
        }
        else if (objectFit === 'cover') {
            const ratio = Math.max(element.width / img.naturalWidth, element.height / img.naturalHeight);
            sourceWidth = element.width / ratio;
            sourceHeight = element.height / ratio;
            sourceX = (img.naturalWidth - sourceWidth) / 2;
            sourceY = (img.naturalHeight - sourceHeight) / 2;
        }
        else if (objectFit === 'fill') {
            // Utiliser les dimensions de l'élément directement
        }
        else if (objectFit === 'scale-down') {
            if (img.naturalWidth > element.width || img.naturalHeight > element.height) {
                const ratio = Math.min(element.width / img.naturalWidth, element.height / img.naturalHeight);
                drawWidth = img.naturalWidth * ratio;
                drawHeight = img.naturalHeight * ratio;
                drawX = (element.width - drawWidth) / 2;
                drawY = (element.height - drawHeight) / 2;
            }
        }
        ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, drawX, drawY, drawWidth, drawHeight);
    }
    else {
        // Image en cours de chargement ou erreur, dessiner un placeholder
        ctx.fillStyle = '#e0e0e0';
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeStyle = '#999999';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
        ctx.fillStyle = '#666666';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(img.complete ? 'Erreur' : 'Chargement...', element.width / 2, element.height / 2);
    }
};
// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawProductTable = (ctx, element, state) => {
    var _a, _b, _c;
    const props = element;
    // ✅ BUGFIX-020: Validate element has minimum size for rendering
    const minWidth = 100;
    const minHeight = 50;
    if (element.width < minWidth || element.height < minHeight) {
        // Element too small, draw placeholder
        ctx.fillStyle = '#f0f0f0';
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.fillStyle = '#999999';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Trop petit', element.width / 2, element.height / 2);
        return;
    }
    const showHeaders = props.showHeaders !== false;
    const showBorders = props.showBorders !== false;
    const showAlternatingRows = props.showAlternatingRows !== false;
    const fontSize = props.fontSize || 11;
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'normal';
    const fontStyle = props.fontStyle || 'normal';
    const showSku = props.showSku !== false;
    const showDescription = props.showDescription !== false;
    const showQuantity = props.showQuantity !== false;
    const showShipping = props.showShipping !== false;
    const showTax = props.showTax !== false;
    const showGlobalDiscount = props.showGlobalDiscount !== false;
    const textColor = normalizeColor(props.textColor || '#000000');
    const borderRadius = props.borderRadius || 0;
    let products;
    let shippingCost;
    let taxRate;
    let globalDiscount;
    let orderFees;
    let currency;
    // Utiliser les données WooCommerce si en mode commande, sinon données fictives
    // ✅ BUGFIX-015: Validate WooCommerceManager access safely
    if (state.previewMode === 'command' && ((_a = wooCommerceManager === null || wooCommerceManager === void 0 ? void 0 : wooCommerceManager.getOrderData) === null || _a === void 0 ? void 0 : _a.call(wooCommerceManager))) {
        const orderData = wooCommerceManager.getOrderData();
        if (orderData) {
            const orderItems = ((_b = wooCommerceManager.getOrderItems) === null || _b === void 0 ? void 0 : _b.call(wooCommerceManager)) || [];
            const orderTotals = ((_c = wooCommerceManager.getOrderTotals) === null || _c === void 0 ? void 0 : _c.call(wooCommerceManager)) || { shipping: 0, tax: 0, subtotal: 0, discount: 0 };
            products = orderItems;
            shippingCost = orderTotals.shipping;
            taxRate = orderTotals.tax > 0 ? (orderTotals.tax / orderTotals.subtotal) * 100 : 20;
            globalDiscount = orderTotals.discount;
            orderFees = 0; // Les frais de commande sont déjà inclus dans les items
            currency = orderData.currency || '€';
        }
        else {
            // Fallback if orderData is null despite passing the check - use demo data
            shippingCost = props.shippingCost || 8.50;
            taxRate = props.taxRate || 20;
            globalDiscount = props.globalDiscount || 5;
            orderFees = props.orderFees || 2.50;
            currency = '€';
            products = [
                {
                    sku: 'DEMO-001',
                    name: 'Sample Product',
                    description: 'Demo product',
                    qty: 1,
                    price: 29.99,
                    discount: 0,
                    total: 29.99
                }
            ];
        }
    }
    else {
        // Données fictives pour le mode éditeur
        shippingCost = props.shippingCost || 8.50;
        taxRate = props.taxRate || 20;
        globalDiscount = props.globalDiscount || 5;
        orderFees = props.orderFees || 2.50;
        currency = '€';
        products = [
            {
                sku: 'TSHIRT-001',
                name: 'T-shirt Premium Bio',
                description: 'T-shirt en coton biologique, coupe slim',
                qty: 2,
                price: 29.99,
                discount: 0,
                total: 59.98
            },
            {
                sku: 'JEAN-045',
                name: 'Jean Slim Fit Noir',
                description: 'Jean stretch confort, taille haute',
                qty: 1,
                price: 89.99,
                discount: 10.00,
                total: 79.99
            },
            {
                sku: 'SHOES-089',
                name: 'Chaussures Running Pro',
                description: 'Chaussures de running avec semelle amortissante',
                qty: 1,
                price: 129.99,
                discount: 0,
                total: 129.99
            },
            {
                sku: 'HOODIE-112',
                name: 'Sweat à Capuche',
                description: 'Sweat molletonné, capuche ajustable',
                qty: 1,
                price: 49.99,
                discount: 5.00,
                total: 44.99
            }
        ];
    }
    // Calcul du total avec remises (même logique pour données fictives et réelles)
    const subtotal = products.reduce((sum, product) => sum + (product.price * product.qty), 0);
    const itemDiscounts = products.reduce((sum, product) => sum + product.discount, 0);
    const subtotalAfterItemDiscounts = subtotal - itemDiscounts;
    // Sous-total incluant les frais de commande
    const subtotalWithOrderFees = subtotalAfterItemDiscounts + orderFees;
    // Appliquer la remise globale sur le sous-total incluant les frais de commande (seulement si affichée)
    const globalDiscountAmount = (globalDiscount > 0 && showGlobalDiscount) ? (subtotalWithOrderFees * globalDiscount / 100) : 0;
    const subtotalAfterGlobalDiscount = subtotalWithOrderFees - globalDiscountAmount; // Ajouter les frais de port (seulement si affichés)
    const subtotalWithShipping = subtotalAfterGlobalDiscount + (showShipping ? shippingCost : 0);
    // Calculer les taxes (seulement si affichées)
    const taxAmount = (taxRate > 0 && showTax) ? (subtotalWithShipping * taxRate / 100) : 0;
    // Total final
    const finalTotal = subtotalWithShipping + taxAmount;
    const columns = [];
    columns.push({ key: 'name', label: 'Produit', width: showSku && showDescription ? 0.35 : showSku || showDescription ? 0.45 : 0.55, align: 'left', x: 0 });
    if (showSku)
        columns.push({ key: 'sku', label: 'SKU', width: 0.15, align: 'left', x: 0 });
    if (showDescription)
        columns.push({ key: 'description', label: 'Description', width: 0.25, align: 'left', x: 0 });
    if (showQuantity)
        columns.push({ key: 'qty', label: 'Qté', width: 0.08, align: 'center', x: 0 });
    columns.push({ key: 'price', label: 'Prix', width: 0.12, align: 'right', x: 0 });
    columns.push({ key: 'total', label: 'Total', width: 0.12, align: 'right', x: 0 });
    // Normaliser les largeurs
    const totalWidth = columns.reduce((sum, col) => sum + col.width, 0);
    columns.forEach(col => col.width = col.width / totalWidth);
    // Calcul des positions X des colonnes
    let currentX = 8;
    columns.forEach(col => {
        col.x = currentX;
        currentX += col.width * (element.width - 16);
    });
    // ✅ Appliquer l'alignement vertical seulement (plus simple et moins risqué)
    const verticalAlign = props.verticalAlign || 'top';
    // Calculer la hauteur totale du tableau pour l'alignement vertical
    const rowHeight = showDescription ? 50 : 35;
    const headerHeight = showHeaders ? 35 : 0;
    const productsCount = products.length;
    const tableHeight = headerHeight + (productsCount * (rowHeight + 4)) + 60; // +60 pour les totaux
    // Offset vertical seulement
    let offsetY = 0;
    // Alignement vertical - déplace le point d'origine vertical du tableau
    if (verticalAlign === 'middle') {
        offsetY = Math.max(0, (element.height - tableHeight) / 2);
    }
    else if (verticalAlign === 'bottom') {
        offsetY = Math.max(0, element.height - tableHeight - 10);
    }
    // Fond
    ctx.fillStyle = props.backgroundColor || '#ffffff';
    ctx.fillRect(0, 0, element.width, element.height);
    // Bordure extérieure
    if (showBorders) {
        ctx.strokeStyle = props.borderColor || '#d1d5db';
        ctx.lineWidth = props.borderWidth || 1;
        if (borderRadius > 0) {
            roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
            ctx.stroke();
        }
        else {
            ctx.strokeRect(0, 0, element.width, element.height);
        }
    }
    ctx.textAlign = 'left';
    let currentY = (showHeaders ? 25 : 15) + offsetY;
    // En-têtes avec style professionnel
    if (showHeaders) {
        ctx.fillStyle = props.headerBackgroundColor || '#f9fafb';
        // Utiliser roundedRect si borderRadius > 0, sinon fillRect normal
        if (borderRadius > 0) {
            roundedRect(ctx, 1, 1 + offsetY, element.width - 2, 32, borderRadius);
            ctx.fill();
        }
        else {
            ctx.fillRect(1, 1 + offsetY, element.width - 2, 32);
        }
        ctx.fillStyle = props.headerTextColor || '#374151';
        ctx.font = `${fontStyle} ${fontWeight} ${fontSize + 1}px ${fontFamily}`;
        ctx.textBaseline = 'top';
        columns.forEach(col => {
            ctx.textAlign = col.align;
            const textX = col.align === 'right' ? col.x + col.width * (element.width - 16) - 4 :
                col.align === 'center' ? col.x + (col.width * (element.width - 16)) / 2 :
                    col.x;
            ctx.fillText(col.label, textX, 10 + offsetY); // Ajusté pour centrer dans la hauteur plus grande
        });
        // Ligne de séparation sous les en-têtes
        ctx.strokeStyle = '#e5e7eb';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(4, 34 + offsetY); // Ajusté pour la nouvelle hauteur
        ctx.lineTo(element.width - 4, 34 + offsetY);
        ctx.stroke();
        currentY = 42 + offsetY; // Ajusté pour la nouvelle hauteur d'entête
    }
    else {
        currentY = 15 + offsetY;
    }
    // Produits avec alternance de couleurs
    ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
    ctx.textBaseline = 'middle';
    products.forEach((product, index) => {
        // Calcul de la position Y absolue pour cette ligne
        const rowY = currentY + index * (rowHeight + 4);
        // Fond alterné pour les lignes (sans bordures)
        if (showAlternatingRows && index % 2 === 1) {
            ctx.fillStyle = props.alternateRowColor || '#f9fafb';
            // Utiliser roundedRect si borderRadius > 0
            if (borderRadius > 0) {
                roundedRect(ctx, 1, rowY, element.width - 2, rowHeight, borderRadius);
                ctx.fill();
            }
            else {
                ctx.fillRect(1, rowY, element.width - 2, rowHeight);
            }
        }
        ctx.fillStyle = textColor; // Utiliser la couleur du texte depuis les propriétés
        columns.forEach(col => {
            ctx.textAlign = col.align;
            const textX = col.align === 'right' ? col.x + col.width * (element.width - 16) - 4 :
                col.align === 'center' ? col.x + (col.width * (element.width - 16)) / 2 :
                    col.x;
            let text = '';
            switch (col.key) {
                case 'name':
                    text = product.name;
                    break;
                case 'sku':
                    text = product.sku;
                    break;
                case 'description':
                    text = product.description;
                    break;
                case 'qty':
                    text = product.qty.toString();
                    break;
                case 'price':
                    text = `${product.price.toFixed(2)}${currency}`;
                    break;
                case 'discount':
                    text = product.discount > 0 ? `${product.discount.toFixed(2)}${currency}` : '-';
                    break;
                case 'total':
                    text = `${product.total.toFixed(2)}${currency}`;
                    break;
            }
            // Gestion du texte qui dépasse
            const maxWidth = col.width * (element.width - 16) - 8;
            if (ctx.measureText(text).width > maxWidth && col.key === 'name') {
                // Tronquer avec "..."
                let truncated = text;
                while (ctx.measureText(truncated + '...').width > maxWidth && truncated.length > 0) {
                    truncated = truncated.slice(0, -1);
                }
                text = truncated + '...';
            }
            ctx.fillText(text, textX, rowY + rowHeight / 2);
        });
    });
    // Positionnement pour la section des totaux (après toutes les lignes de produits)
    currentY = 55 + products.length * (rowHeight + 4) + 8;
    // Section des totaux
    // Ligne de séparation avant les totaux
    ctx.strokeStyle = '#d1d5db';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(element.width - 200, currentY);
    ctx.lineTo(element.width - 8, currentY);
    ctx.stroke();
    currentY += 20;
    // Affichage des totaux
    ctx.font = `bold ${fontSize}px Arial`;
    ctx.fillStyle = textColor; // Utiliser la couleur du texte
    ctx.textAlign = 'left';
    const totalsY = currentY;
    ctx.fillText('Sous-total:', element.width - 200, totalsY);
    ctx.textAlign = 'right';
    ctx.fillText(`${subtotalWithOrderFees.toFixed(2)}${currency}`, element.width - 8, totalsY);
    currentY += 18;
    // Remises combinées (articles + globale) - proviennent de coupons WooCommerce
    const totalDiscounts = itemDiscounts + (showGlobalDiscount ? globalDiscountAmount : 0);
    if (totalDiscounts > 0) {
        ctx.textAlign = 'left';
        ctx.fillStyle = '#059669'; // Garder le vert pour la remise (couleur spéciale)
        ctx.fillText('Coupon:', element.width - 200, currentY);
        ctx.textAlign = 'right';
        ctx.fillText(`-${totalDiscounts.toFixed(2)}${currency}`, element.width - 8, currentY);
        currentY += 18;
    }
    // Frais de port
    if (shippingCost > 0 && showShipping) {
        ctx.textAlign = 'left';
        ctx.fillStyle = textColor; // Utiliser la couleur du texte
        ctx.fillText('Frais de port:', element.width - 200, currentY);
        ctx.textAlign = 'right';
        ctx.fillText(`${shippingCost.toFixed(2)}${currency}`, element.width - 8, currentY);
        currentY += 18;
    }
    // Taxes
    if (taxAmount > 0 && showTax) {
        ctx.textAlign = 'left';
        ctx.fillStyle = textColor; // Utiliser la couleur du texte
        ctx.fillText(`TVA (${taxRate}%):`, element.width - 200, currentY);
        ctx.textAlign = 'right';
        ctx.fillText(`${taxAmount.toFixed(2)}${currency}`, element.width - 8, currentY);
        currentY += 18;
    }
    currentY += 8; // Plus d'espace avant la ligne de séparation du total
    ctx.strokeStyle = textColor; // Utiliser la couleur du texte pour la ligne
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(element.width - 200, currentY - 5);
    ctx.lineTo(element.width - 8, currentY - 5);
    ctx.stroke();
    currentY += 8; // Plus d'espace après la ligne de séparation
    ctx.font = `${fontStyle} bold ${fontSize + 2}px ${fontFamily}`;
    ctx.fillStyle = textColor; // Utiliser la couleur du texte pour le total
    ctx.textAlign = 'left';
    ctx.fillText('TOTAL:', element.width - 200, currentY);
    ctx.textAlign = 'right';
    ctx.fillText(`${finalTotal.toFixed(2)}${currency}`, element.width - 8, currentY);
};
// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawCustomerInfo = (ctx, element, state) => {
    const props = element;
    const fontSize = props.fontSize || 12;
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'normal';
    const fontStyle = props.fontStyle || 'normal';
    // Propriétés de police pour l'en-tête
    const headerFontSize = props.headerFontSize || fontSize + 2;
    const headerFontFamily = props.headerFontFamily || fontFamily;
    const headerFontWeight = props.headerFontWeight || fontWeight;
    const headerFontStyle = props.headerFontStyle || fontStyle;
    // Propriétés de police pour le corps du texte
    const bodyFontSize = props.bodyFontSize || fontSize;
    const bodyFontFamily = props.bodyFontFamily || fontFamily;
    const bodyFontWeight = props.bodyFontWeight || fontWeight;
    const bodyFontStyle = props.bodyFontStyle || fontStyle;
    const layout = props.layout || 'vertical';
    const showHeaders = props.showHeaders !== false;
    const showBorders = props.showBorders !== false;
    const showFullName = props.showFullName !== false;
    const showAddress = props.showAddress !== false;
    const showEmail = props.showEmail !== false;
    const showPhone = props.showPhone !== false;
    const showPaymentMethod = props.showPaymentMethod !== false;
    const showTransactionId = props.showTransactionId !== false;
    // Fond
    if (props.showBackground !== false) {
        ctx.fillStyle = props.backgroundColor || '#ffffff';
        ctx.fillRect(0, 0, element.width, element.height);
    }
    // Bordures
    if (showBorders) {
        ctx.strokeStyle = props.borderColor || '#e5e7eb';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
    }
    ctx.fillStyle = normalizeColor(props.textColor || '#000000');
    ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
    ctx.textAlign = 'left';
    let y = showHeaders ? 25 : 15;
    // En-tête
    if (showHeaders) {
        ctx.fillStyle = normalizeColor(props.headerTextColor || '#111827');
        ctx.fillText('Informations Client', 10, y);
        y += 20;
        ctx.fillStyle = normalizeColor(props.textColor || '#000000');
    }
    // Informations client fictives ou réelles selon le mode
    let customerData;
    if (state.previewMode === 'command') {
        customerData = wooCommerceManager.getCustomerInfo();
    }
    else {
        // Données fictives pour le mode éditeur
        customerData = {
            name: 'Marie Dupont',
            address: '15 rue des Lilas, 75001 Paris',
            email: 'marie.dupont@email.com',
            phone: '+33 6 12 34 56 78'
        };
    }
    ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;
    if (layout === 'vertical') {
        if (showFullName) {
            ctx.fillText(customerData.name, 10, y);
            y += 18;
        }
        if (showAddress) {
            ctx.fillText(customerData.address, 10, y);
            y += 18;
        }
        if (showEmail) {
            ctx.fillText(customerData.email, 10, y);
            y += 18;
        }
        if (showPhone) {
            ctx.fillText(customerData.phone, 10, y);
            y += 18;
        }
        if (showPaymentMethod) {
            ctx.fillText('Paiement: Carte bancaire', 10, y);
            y += 18;
        }
        if (showTransactionId) {
            ctx.fillText('ID: TXN123456789', 10, y);
        }
    }
    else if (layout === 'horizontal') {
        let text = '';
        if (showFullName)
            text += customerData.name;
        if (showEmail)
            text += (text ? ' - ' : '') + customerData.email;
        if (text)
            ctx.fillText(text, 10, y);
        if (showPhone) {
            ctx.fillText(customerData.phone, element.width - ctx.measureText(customerData.phone).width - 10, y);
        }
    }
    else if (layout === 'compact') {
        let compactText = '';
        if (showFullName)
            compactText += customerData.name;
        if (showAddress)
            compactText += (compactText ? ' • ' : '') + customerData.address.split(',')[0];
        if (showEmail)
            compactText += (compactText ? ' • ' : '') + customerData.email;
        if (showPhone)
            compactText += (compactText ? ' • ' : '') + customerData.phone;
        // Wrap text if too long
        const maxWidth = element.width - 20;
        const words = compactText.split(' ');
        let line = '';
        let compactY = y;
        for (let i = 0; i < words.length; i++) {
            const testLine = line + words[i] + ' ';
            const metrics = ctx.measureText(testLine);
            if (metrics.width > maxWidth && i > 0) {
                ctx.fillText(line, 10, compactY);
                line = words[i] + ' ';
                compactY += 16;
            }
            else {
                line = testLine;
            }
        }
        ctx.fillText(line, 10, compactY);
    }
};
const drawCompanyInfo = (ctx, element, canvasSettings) => {
    const props = element;
    const fontSize = props.fontSize || 12;
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'normal';
    const fontStyle = props.fontStyle || 'normal';
    // Propriétés de police pour l'en-tête (nom de l'entreprise)
    const headerFontSize = props.headerFontSize || Math.round(fontSize * 1.2);
    const headerFontFamily = props.headerFontFamily || fontFamily;
    const headerFontWeight = props.headerFontWeight || 'bold';
    const headerFontStyle = props.headerFontStyle || fontStyle;
    // Propriétés de police pour le corps du texte
    const bodyFontSize = props.bodyFontSize || fontSize;
    const bodyFontFamily = props.bodyFontFamily || fontFamily;
    const bodyFontWeight = props.bodyFontWeight || fontWeight;
    const bodyFontStyle = props.bodyFontStyle || fontStyle;
    const textAlign = 'left'; // Forcer alignement à gauche pour company_info
    const theme = (props.theme || 'corporate');
    const showBackground = props.showBackground !== false; // Par défaut true
    const showBorders = props.showBorders !== false; // Par défaut true
    const showCompanyName = props.showCompanyName !== false; // Par défaut true
    const showAddress = props.showAddress !== false; // Par défaut true
    const showPhone = props.showPhone !== false; // Par défaut true
    const showEmail = props.showEmail !== false; // Par défaut true
    const showSiret = props.showSiret !== false; // Par défaut true
    const showVat = props.showVat !== false; // Par défaut true
    const showRcs = props.showRcs !== false; // Par défaut true
    const showCapital = props.showCapital !== false; // Par défaut true
    // Définition des thèmes
    const themes = {
        corporate: {
            backgroundColor: '#ffffff',
            borderColor: '#1f2937',
            textColor: '#374151',
            headerTextColor: '#111827'
        },
        modern: {
            backgroundColor: '#ffffff',
            borderColor: '#3b82f6',
            textColor: '#1e40af',
            headerTextColor: '#1e3a8a'
        },
        elegant: {
            backgroundColor: '#ffffff',
            borderColor: '#8b5cf6',
            textColor: '#6d28d9',
            headerTextColor: '#581c87'
        },
        minimal: {
            backgroundColor: '#ffffff',
            borderColor: '#e5e7eb',
            textColor: '#374151',
            headerTextColor: '#111827'
        },
        professional: {
            backgroundColor: '#ffffff',
            borderColor: '#059669',
            textColor: '#047857',
            headerTextColor: '#064e3b'
        }
    };
    const currentTheme = themes[theme] || themes.corporate;
    // Utiliser les couleurs depuis les props de l'élément ou le thème (PAS les paramètres canvas globaux)
    const bgColor = normalizeColor(props.backgroundColor || currentTheme.backgroundColor);
    const borderCol = normalizeColor(props.borderColor || currentTheme.borderColor);
    const txtColor = normalizeColor(props.textColor || currentTheme.textColor);
    const headerTxtColor = normalizeColor(props.headerTextColor || currentTheme.headerTextColor);
    // Appliquer le fond si demandé
    if (showBackground) {
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, element.width, element.height);
    }
    // Appliquer les bordures si demandé
    if (showBorders) {
        ctx.strokeStyle = borderCol;
        ctx.lineWidth = props.borderWidth || 1;
        ctx.strokeRect(0, 0, element.width, element.height);
    }
    ctx.fillStyle = txtColor;
    ctx.textAlign = textAlign;
    // Calcul de la position X (toujours aligné à gauche pour company_info)
    let x = 10;
    let y = 20;
    // Informations entreprise hybrides : props configurables + valeurs par défaut
    const companyData = {
        name: props.companyName || 'Ma Boutique En Ligne',
        address: props.companyAddress || '25 avenue des Commerçants',
        city: props.companyCity || '69000 Lyon',
        siret: props.companySiret || 'SIRET: 123 456 789 00012',
        tva: props.companyTva || 'TVA: FR 12 345 678 901',
        rcs: props.companyRcs || 'RCS: Lyon B 123 456 789',
        capital: props.companyCapital || 'Capital social: 10 000 €',
        email: props.companyEmail || 'contact@maboutique.com',
        phone: props.companyPhone || '+33 4 12 34 56 78'
    };
    // Afficher le nom de l'entreprise si demandé
    if (showCompanyName) {
        ctx.fillStyle = headerTxtColor;
        ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
        ctx.fillText(companyData.name, x, y);
        y += Math.round(fontSize * 1.5);
        ctx.fillStyle = txtColor;
    }
    // Police normale pour les autres éléments
    ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;
    // Afficher l'adresse si demandée
    if (showAddress) {
        ctx.fillText(companyData.address, x, y);
        y += Math.round(fontSize * 1.2);
        ctx.fillText(companyData.city, x, y);
        y += Math.round(fontSize * 1.5);
    }
    // Afficher le SIRET si demandé
    if (showSiret) {
        ctx.fillText(companyData.siret, x, y);
        y += Math.round(fontSize * 1.2);
    }
    // Afficher la TVA si demandée
    if (showVat) {
        ctx.fillText(companyData.tva, x, y);
        y += Math.round(fontSize * 1.2);
    }
    // Afficher le RCS si demandé
    if (showRcs) {
        ctx.fillText(companyData.rcs, x, y);
        y += Math.round(fontSize * 1.2);
    }
    // Afficher le Capital social si demandé
    if (showCapital) {
        ctx.fillText(companyData.capital, x, y);
        y += Math.round(fontSize * 1.2);
    }
    // Afficher l'email si demandé
    if (showEmail) {
        ctx.fillText(companyData.email, x, y);
        y += Math.round(fontSize * 1.2);
    }
    // Afficher le téléphone si demandé
    if (showPhone) {
        ctx.fillText(companyData.phone, x, y);
    }
};
const drawOrderNumber = (ctx, element, state) => {
    const props = element;
    const fontSize = props.fontSize || 14;
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'normal';
    const fontStyle = props.fontStyle || 'normal';
    // Propriétés de police pour le label
    const labelFontSize = props.headerFontSize || fontSize;
    const labelFontFamily = props.headerFontFamily || fontFamily;
    const labelFontWeight = props.headerFontWeight || 'bold';
    const labelFontStyle = props.headerFontStyle || fontStyle;
    // Propriétés de police pour le numéro
    const numberFontSize = props.numberFontSize || fontSize;
    const numberFontFamily = props.numberFontFamily || fontFamily;
    const numberFontWeight = props.numberFontWeight || fontWeight;
    const numberFontStyle = props.numberFontStyle || fontStyle;
    // Propriétés de police pour la date
    const dateFontSize = props.dateFontSize || (fontSize - 2);
    const dateFontFamily = props.dateFontFamily || fontFamily;
    const dateFontWeight = props.dateFontWeight || fontWeight;
    const dateFontStyle = props.dateFontStyle || fontStyle;
    // const textAlign = props.textAlign || 'left'; // left, center, right
    // Propriétés d'alignement spécifiques
    // const labelTextAlign = props.labelTextAlign || textAlign;
    // const numberTextAlign = props.numberTextAlign || textAlign;
    // const dateTextAlign = props.dateTextAlign || textAlign;
    const contentAlign = props.contentAlign || 'left'; // Alignement général du contenu dans l'élément
    const showLabel = props.showLabel !== false; // Par défaut true
    const showDate = props.showDate !== false; // Par défaut true
    const labelPosition = props.labelPosition || 'above'; // above, left, right, below
    const labelText = props.labelText || 'N° de commande:'; // Texte personnalisable du libellé
    // Fonction helper pour calculer la position X selon l'alignement général du contenu
    // const calculateContentX = (align: string) => {
    //   if (align === 'left') {
    //     return 10;
    //   } else if (align === 'center') {
    //     return element.width / 2;
    //   } else { // right
    //     return element.width - 10;
    //   }
    // };
    // Fonction helper pour calculer la position X selon l'alignement du texte
    // const calculateX = (align: string) => {
    //   if (align === 'left') {
    //     return 10;
    //   } else if (align === 'center') {
    //     return element.width / 2;
    //   } else { // right
    //     return element.width - 10;
    //   }
    // };
    // Appliquer le fond seulement si showBackground est activé
    if (props.showBackground !== false) {
        ctx.fillStyle = props.backgroundColor || '#e5e7eb';
        ctx.fillRect(0, 0, element.width, element.height);
    }
    ctx.fillStyle = '#000000';
    // Numéro de commande et date fictifs ou réels selon le mode
    let orderNumber;
    let orderDate;
    if (state.previewMode === 'command') {
        orderNumber = wooCommerceManager.getOrderNumber();
        orderDate = wooCommerceManager.getOrderDate();
    }
    else {
        // Utiliser les données WooCommerce si disponibles, sinon valeurs par défaut
        orderNumber = wooCommerceManager.getOrderNumber() || 'CMD-2024-01234';
        orderDate = wooCommerceManager.getOrderDate() || '27/10/2024';
    }
    let y = 20;
    // Calculer la largeur totale du contenu pour l'alignement général
    let totalContentWidth = 0;
    if (showLabel) {
        if (labelPosition === 'above' || labelPosition === 'below') {
            // Pour les positions verticales, prendre la largeur maximale
            ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
            const labelWidth = ctx.measureText(labelText).width;
            ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
            const numberWidth = ctx.measureText(orderNumber).width;
            totalContentWidth = Math.max(labelWidth, numberWidth);
        }
        else {
            // Pour les positions latérales, calculer la largeur combinée
            ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
            const labelWidth = ctx.measureText(labelText).width;
            ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
            const numberWidth = ctx.measureText(orderNumber).width;
            totalContentWidth = labelWidth + numberWidth + 15; // 15px d'espace
        }
    }
    else {
        // Juste le numéro
        ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
        totalContentWidth = ctx.measureText(orderNumber).width;
    }
    // Calculer le décalage pour l'alignement général du contenu
    let contentOffsetX = 0;
    if (contentAlign === 'center') {
        contentOffsetX = (element.width - totalContentWidth) / 2 - 10; // -10 car on commence à 10
    }
    else if (contentAlign === 'right') {
        contentOffsetX = element.width - totalContentWidth - 20; // -20 pour les marges
    }
    if (showLabel) {
        if (labelPosition === 'above') {
            // Libellé au-dessus, numéro en-dessous - utiliser l'alignement général du contenu
            ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
            ctx.textAlign = contentAlign;
            const labelX = contentAlign === 'left' ? 10 + contentOffsetX :
                contentAlign === 'center' ? element.width / 2 :
                    element.width - 10;
            ctx.fillText(labelText, labelX, y);
            y += 18;
            ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
            ctx.textAlign = contentAlign;
            const numberX = contentAlign === 'left' ? 10 + contentOffsetX :
                contentAlign === 'center' ? element.width / 2 :
                    element.width - 10;
            ctx.fillText(orderNumber, numberX, y);
        }
        else if (labelPosition === 'below') {
            // Numéro au-dessus, libellé en-dessous - utiliser l'alignement général du contenu
            ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
            ctx.textAlign = contentAlign;
            const numberX = contentAlign === 'left' ? 10 + contentOffsetX :
                contentAlign === 'center' ? element.width / 2 :
                    element.width - 10;
            ctx.fillText(orderNumber, numberX, y);
            y += 18;
            ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
            ctx.textAlign = contentAlign;
            const labelX = contentAlign === 'left' ? 10 + contentOffsetX :
                contentAlign === 'center' ? element.width / 2 :
                    element.width - 10;
            ctx.fillText(labelText, labelX, y);
        }
        else if (labelPosition === 'left') {
            // Libellé à gauche, numéro à droite - avec espacement optimal et alignement général
            ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
            ctx.textAlign = 'left';
            const labelX = 10 + contentOffsetX;
            ctx.fillText(labelText, labelX, y);
            // Calculer l'espace disponible pour centrer le numéro ou l'aligner intelligemment
            const labelWidth = ctx.measureText(labelText).width;
            const numberX = labelX + labelWidth + 15; // 15px d'espace après le libellé
            ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
            ctx.textAlign = 'left';
            ctx.fillText(orderNumber, numberX, y);
        }
        else if (labelPosition === 'right') {
            // Numéro à gauche, libellé à droite - avec espacement optimal et alignement général
            ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
            ctx.textAlign = 'left';
            const numberX = 10 + contentOffsetX;
            ctx.fillText(orderNumber, numberX, y);
            // Calculer la position du libellé après le numéro
            const numberWidth = ctx.measureText(orderNumber).width;
            const labelX = numberX + numberWidth + 15; // 15px d'espace après le numéro
            ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
            ctx.textAlign = 'left';
            ctx.fillText(labelText, labelX, y);
        }
    }
    else {
        // Pas de libellé, juste le numéro avec alignement général du contenu
        ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
        ctx.textAlign = contentAlign;
        // Pour le cas sans libellé, utiliser directement calculateContentX sans contentOffsetX
        // car contentOffsetX est calculé pour centrer le contenu total, mais ici on n'a que le numéro
        if (contentAlign === 'left') {
            ctx.fillText(orderNumber, 10, y);
        }
        else if (contentAlign === 'center') {
            ctx.fillText(orderNumber, element.width / 2, y);
        }
        else { // right
            ctx.fillText(orderNumber, element.width - 10, y);
        }
    }
    // Afficher la date sur une nouvelle ligne avec le même alignement général
    if (showDate) {
        ctx.font = `${dateFontStyle} ${dateFontWeight} ${dateFontSize}px ${dateFontFamily}`;
        ctx.textAlign = contentAlign;
        // Pour la date, utiliser directement calculateContentX sans contentOffsetX
        // car contentOffsetX est calculé pour centrer le contenu total
        if (contentAlign === 'left') {
            ctx.fillText(`Date: ${orderDate}`, 10, y + 20);
        }
        else if (contentAlign === 'center') {
            ctx.fillText(`Date: ${orderDate}`, element.width / 2, y + 20);
        }
        else { // right
            ctx.fillText(`Date: ${orderDate}`, element.width - 10, y + 20);
        }
    }
};
const drawDocumentType = (ctx, element, state) => {
    const props = element;
    const fontSize = props.fontSize || 18;
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'bold';
    const fontStyle = props.fontStyle || 'normal';
    const textAlign = props.textAlign || 'left';
    const textColor = props.textColor || '#000000';
    // Appliquer le fond seulement si showBackground est activé
    if (props.showBackground !== false) {
        ctx.fillStyle = props.backgroundColor || '#e5e7eb';
        ctx.fillRect(0, 0, element.width, element.height);
    }
    ctx.fillStyle = textColor;
    ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
    ctx.textAlign = textAlign;
    // Type de document fictif ou réel selon le mode
    let documentType;
    if (state.previewMode === 'command') {
        // En mode commande réel, on pourrait récupérer le type depuis WooCommerce
        // Pour l'instant, on utilise la valeur configurée ou une valeur par défaut
        documentType = props.documentType || 'FACTURE';
    }
    else {
        // Données fictives pour le mode éditeur
        documentType = props.documentType || 'FACTURE';
    }
    // Convertir les valeurs techniques en texte lisible
    const documentTypeLabels = {
        'FACTURE': 'FACTURE',
        'DEVIS': 'DEVIS',
        'BON_COMMANDE': 'BON DE COMMANDE',
        'AVOIR': 'AVOIR',
        'RELEVE': 'RELEVE',
        'CONTRAT': 'CONTRAT'
    };
    documentType = documentTypeLabels[documentType] || documentType;
    const x = textAlign === 'center' ? element.width / 2 : textAlign === 'right' ? element.width - 10 : 10;
    const y = element.height / 2 + fontSize / 3; // Centrer verticalement
    ctx.fillText(documentType, x, y);
};
// Flag global pour afficher les logs détaillés des éléments (debug)
// Debug flags - set to true to enable verbose logging
// Constantes pour le cache des images
const MAX_CACHE_ITEMS = 100; // Max 100 images in cache
const Canvas = function Canvas({ width, height, className }) {
    var _a;
    const canvasRef = (0,react.useRef)(null);
    // ✅ Track derniers éléments rendus pour éviter double rendu
    const lastRenderedElementsRef = (0,react.useRef)('');
    const renderCountRef = (0,react.useRef)(0);
    const { state, dispatch } = useBuilder();
    const canvasSettings = CanvasSettingsContext_useCanvasSettings();
    debugLog('🎨 Canvas: Component initialized with props:', { width, height, className });
    debugLog('📊 Canvas: Initial state:', { elements: state.elements.length, selection: state.selection.selectedElements.length, zoom: state.canvas.zoom });
    debugLog(`[Canvas] Component initialized - Dimensions: ${width}x${height}, Settings loaded: ${!!canvasSettings}`);
    // Force re-render when canvas settings change (commenté pour éviter les boucles)
    // const [, forceUpdate] = useState({});
    // useEffect(() => {
    //   forceUpdate({});
    // }, [canvasSettings.canvasBackgroundColor, canvasSettings.borderColor, canvasSettings.borderWidth, canvasSettings.shadowEnabled, canvasSettings.containerBackgroundColor]);
    // État pour le menu contextuel
    const [contextMenu, setContextMenu] = (0,react.useState)({
        isVisible: false,
        position: { x: 0, y: 0 }
    });
    // ✅ STATE for image loading - force redraw when images load
    const [imageLoadCount, setImageLoadCount] = (0,react.useState)(0);
    // Récupérer la limite mémoire JavaScript depuis les paramètres
    const memoryLimitJs = useCanvasSetting('memory_limit_js', 256); // En MB, défaut 256MB
    // ✅ LAZY LOADING: Récupérer le paramètre depuis les settings
    const lazyLoadingEnabled = canvasSettings.lazyLoadingEditor;
    // ✅ LAZY LOADING: État pour tracker les éléments visibles
    const [visibleElements, setVisibleElements] = (0,react.useState)(new Set());
    const [viewportBounds, setViewportBounds] = (0,react.useState)({ x: 0, y: 0, width: width, height: height });
    // ✅ LAZY LOADING: Fonction pour déterminer si un élément est visible dans le viewport
    const isElementVisible = (0,react.useCallback)((element, viewport) => {
        // Calculer les bounds de l'élément (simplifié - on pourrait améliorer avec rotation, etc.)
        const elementBounds = {
            x: element.x,
            y: element.y,
            width: element.width,
            height: element.height
        };
        // Vérifier si l'élément intersecte le viewport (avec une marge de 100px)
        const margin = 100;
        return !(elementBounds.x + elementBounds.width < viewportBounds.x - margin ||
            elementBounds.x > viewportBounds.x + viewportBounds.width + margin ||
            elementBounds.y + elementBounds.height < viewportBounds.y - margin ||
            elementBounds.y > viewportBounds.y + viewportBounds.height + margin);
    }, [viewportBounds]); // ✅ LAZY LOADING: Filtrer les éléments visibles
    const visibleElementsList = (0,react.useMemo)(() => {
        if (!lazyLoadingEnabled) {
            return state.elements; // Tous les éléments si lazy loading désactivé
        }
        // Toujours inclure les 5 premiers éléments pour éviter les sauts visuels
        const alwaysVisible = state.elements.slice(0, 5);
        const potentiallyVisible = state.elements.slice(5).filter(element => isElementVisible(element, viewportBounds));
        return [...alwaysVisible, ...potentiallyVisible];
    }, [state.elements, lazyLoadingEnabled, viewportBounds, isElementVisible]);
    // Cache pour les images chargées avec métadonnées de mémoire
    const imageCache = (0,react.useRef)(new Map());
    // ✅ LAZY LOADING: Hook pour mettre à jour le viewport quand le canvas change
    (0,react.useEffect)(() => {
        if (!canvasRef.current)
            return;
        const updateViewport = () => {
            const canvas = canvasRef.current;
            if (!canvas)
                return;
            const rect = canvas.getBoundingClientRect();
            setViewportBounds({
                x: -rect.left,
                y: -rect.top,
                width: window.innerWidth,
                height: window.innerHeight
            });
        };
        // Mettre à jour initialement
        updateViewport();
        // Écouter les changements de scroll et resize avec passive: true
        window.addEventListener('scroll', updateViewport, { passive: true });
        window.addEventListener('resize', updateViewport, { passive: true });
        return () => {
            window.removeEventListener('scroll', updateViewport);
            window.removeEventListener('resize', updateViewport);
        };
    }, []);
    // ✅ CORRECTION 7: Tracker les URLs rendues pour détecter changements
    const renderedLogoUrlsRef = (0,react.useRef)(new Map()); // elementId -> logoUrl
    // ✅ Flag: Track if we've done initial render check for images
    const initialImageCheckDoneRef = (0,react.useRef)(false);
    // Fonction pour estimer la taille mémoire d'une image (approximation)
    const estimateImageMemorySize = (0,react.useCallback)((img) => {
        // Estimation basée sur : largeur * hauteur * 4 octets (RGBA) + overhead
        const pixelData = img.naturalWidth * img.naturalHeight * 4;
        const overhead = 1024; // Overhead approximatif par image
        return pixelData + overhead;
    }, []);
    // Fonction pour calculer l'usage mémoire total du cache
    const calculateCacheMemoryUsage = (0,react.useCallback)(() => {
        let totalSize = 0;
        for (const [, data] of imageCache.current) {
            totalSize += data.size;
        }
        return totalSize / (1024 * 1024); // Convertir en MB
    }, []);
    // Fonction pour vérifier si la limite mémoire est dépassée
    const isMemoryLimitExceeded = (0,react.useCallback)(() => {
        const currentUsage = calculateCacheMemoryUsage();
        const limit = memoryLimitJs;
        // Vérifier aussi la mémoire globale du navigateur si disponible
        if ('memory' in performance) {
            const perfMemory = performance.memory;
            const browserMemoryUsage = perfMemory.usedJSHeapSize / (1024 * 1024); // MB
            const browserLimit = perfMemory.jsHeapSizeLimit / (1024 * 1024); // MB
            // Si le navigateur approche sa limite, être plus agressif
            if (browserMemoryUsage > browserLimit * 0.8) {
                debugWarn(`[Canvas Memory] Browser memory usage high: ${browserMemoryUsage.toFixed(1)}MB / ${browserLimit.toFixed(1)}MB`);
                return true;
            }
        }
        return currentUsage > limit * 0.8; // Déclencher le nettoyage à 80% de la limite
    }, [calculateCacheMemoryUsage, memoryLimitJs]);
    // ✅ CORRECTION 2: Fonction pour nettoyer le cache des images avec gestion mémoire
    const cleanupImageCache = (0,react.useCallback)(() => {
        const cache = imageCache.current;
        const currentMemoryUsage = calculateCacheMemoryUsage();
        const memoryLimit = memoryLimitJs;
        debugLog(`[Canvas Memory] Starting cache cleanup - Current usage: ${currentMemoryUsage.toFixed(2)}MB, Limit: ${memoryLimit}MB, Items: ${cache.size}`);
        // Nettoyer si limite dépassée ou trop d'éléments
        if (isMemoryLimitExceeded() || cache.size > MAX_CACHE_ITEMS) {
            // Trier par date d'utilisation (LRU - Least Recently Used)
            const entries = Array.from(cache.entries()).sort(([, a], [, b]) => a.lastUsed - b.lastUsed);
            // Calculer combien supprimer pour revenir sous 70% de la limite
            const targetMemoryUsage = memoryLimit * 0.7;
            let memoryToFree = Math.max(0, currentMemoryUsage - targetMemoryUsage);
            let itemsToRemove = Math.min(20, Math.ceil(cache.size * 0.2)); // Au moins 20% des éléments ou 20 éléments max
            let removed = 0;
            let memoryFreed = 0;
            for (const [url, data] of entries) {
                if (removed >= itemsToRemove && memoryFreed >= memoryToFree)
                    break;
                cache.delete(url);
                memoryFreed += data.size / (1024 * 1024); // MB
                removed++;
                debugLog(`[Canvas Memory] Removed image from cache: ${url.split('/').pop()}, Freed: ${(data.size / (1024 * 1024)).toFixed(2)}MB`);
            }
            debugLog(`[Canvas Memory] Cache cleanup completed - Removed ${removed} items, Freed ${memoryFreed.toFixed(2)}MB, New usage: ${(currentMemoryUsage - memoryFreed).toFixed(2)}MB`);
        }
        else {
            debugLog(`[Canvas Memory] Cache cleanup not needed - Usage within limits`);
        }
    }, [calculateCacheMemoryUsage, memoryLimitJs, isMemoryLimitExceeded]);
    // Fonction pour forcer un nettoyage manuel (utile pour le débogage)
    const forceCacheCleanup = (0,react.useCallback)(() => {
        cleanupImageCache();
    }, [cleanupImageCache]);
    // Exposer les fonctions de gestion mémoire globalement pour le débogage
    (0,react.useEffect)(() => {
        window.canvasMemoryDebug = {
            getCacheStats: () => ({
                itemCount: imageCache.current.size,
                memoryUsage: calculateCacheMemoryUsage(),
                memoryLimit: memoryLimitJs,
                items: Array.from(imageCache.current.entries()).map(([url, data]) => ({
                    url: url.split('/').pop(),
                    size: (data.size / (1024 * 1024)).toFixed(2) + 'MB',
                    lastUsed: new Date(data.lastUsed).toLocaleTimeString()
                }))
            }),
            forceCleanup: forceCacheCleanup,
            getBrowserMemory: () => {
                if ('memory' in performance) {
                    const perfMemory = performance.memory;
                    return {
                        used: (perfMemory.usedJSHeapSize / (1024 * 1024)).toFixed(1) + 'MB',
                        total: (perfMemory.totalJSHeapSize / (1024 * 1024)).toFixed(1) + 'MB',
                        limit: (perfMemory.jsHeapSizeLimit / (1024 * 1024)).toFixed(1) + 'MB'
                    };
                }
                return { error: 'Performance.memory not available' };
            }
        };
        return () => {
            delete window.canvasMemoryDebug;
        };
    }, [calculateCacheMemoryUsage, memoryLimitJs, forceCacheCleanup]); // Surveillance périodique de la mémoire globale du navigateur
    (0,react.useEffect)(() => {
        const memoryCheckInterval = setInterval(() => {
            if ('memory' in performance) {
                const perfMemory = performance.memory;
                const browserMemoryUsage = perfMemory.usedJSHeapSize / (1024 * 1024); // MB
                const browserLimit = perfMemory.jsHeapSizeLimit / (1024 * 1024); // MB
                const cacheMemoryUsage = calculateCacheMemoryUsage();
                // Log détaillé de la mémoire si activé
                if (canvasSettings.debugMode) {
                }
                // Nettoyage d'urgence si mémoire critique
                if (browserMemoryUsage > browserLimit * 0.9) {
                    debugWarn(`[Canvas Memory] Critical memory usage! Forcing cache cleanup...`);
                    cleanupImageCache();
                }
            }
        }, 10000); // Vérification toutes les 10 secondes
        return () => clearInterval(memoryCheckInterval);
    }, [calculateCacheMemoryUsage, memoryLimitJs, cleanupImageCache, canvasSettings.debugMode]);
    // Écouter les changements de couleur de fond depuis les paramètres
    (0,react.useEffect)(() => {
        debugLog(`[Canvas] Background color change detected: ${canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.canvasBackgroundColor}`);
        const handleBgColorChange = (event) => {
            debugLog(`[Canvas] Custom background color change event received:`, event.detail);
            // Forcer le re-rendu du canvas avec la nouvelle couleur
            renderCountRef.current += 1;
            // Le canvas se re-rendra automatiquement grâce aux dépendances du useEffect principal
        };
        window.addEventListener('pdfBuilderCanvasBgColorChanged', handleBgColorChange, { passive: true });
        return () => {
            window.removeEventListener('pdfBuilderCanvasBgColorChanged', handleBgColorChange);
        };
    }, [canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.canvasBackgroundColor]);
    // Utiliser les hooks pour les interactions
    const { handleDrop, handleDragOver, handleDragLeave, isDragOver } = useCanvasDrop({
        canvasRef,
        canvasWidth: width,
        canvasHeight: height,
        elements: state.elements || [],
        dragEnabled: (_a = canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.selectionDragEnabled) !== null && _a !== void 0 ? _a : true
    });
    const { handleCanvasClick, handleMouseDown, handleMouseMove, handleMouseUp, handleContextMenu, selectionState } = useCanvasInteraction({
        canvasRef,
        canvasWidth: width,
        canvasHeight: height
    });
    // Hook pour les raccourcis clavier
    const keyboardShortcutInfo = useKeyboardShortcuts();
    // Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
    // Fonction helper pour dessiner un placeholder de logo
    const drawLogoPlaceholder = (0,react.useCallback)((ctx, element, alignment, text) => {
        const logoWidth = Math.min(element.width - 20, 120);
        const logoHeight = Math.min(element.height - 20, 60);
        let x = 10;
        if (alignment === 'center') {
            x = (element.width - logoWidth) / 2;
        }
        else if (alignment === 'right') {
            x = element.width - logoWidth - 10;
        }
        const y = (element.height - logoHeight) / 2;
        // Rectangle du logo
        ctx.fillStyle = '#f0f0f0';
        ctx.strokeStyle = '#ccc';
        ctx.lineWidth = 1;
        ctx.fillRect(x, y, logoWidth, logoHeight);
        ctx.strokeRect(x, y, logoWidth, logoHeight);
        // Texte du placeholder
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(text, x + logoWidth / 2, y + logoHeight / 2 + 4);
    }, []);
    const drawCompanyLogo = (0,react.useCallback)((ctx, element) => {
        const props = element;
        const logoUrl = props.src || props.logoUrl || '';
        // ✅ FIX: If no logo URL, show a better placeholder
        if (!logoUrl) {
            drawLogoPlaceholder(ctx, element, 'center', 'Configurez le logo entreprise');
            return;
        }
        // const fit = props.fit || 'contain';
        const alignment = props.alignment || 'left';
        // ✅ CORRECTION 7: Détecter si l'URL a changé
        const lastRenderedUrl = renderedLogoUrlsRef.current.get(element.id);
        if (logoUrl !== lastRenderedUrl) {
            renderedLogoUrlsRef.current.set(element.id, logoUrl);
        }
        // Fond transparent
        ctx.fillStyle = 'transparent';
        ctx.fillRect(0, 0, element.width, element.height);
        if (logoUrl) {
            // Vérifier si l'image est en cache
            let cachedImage = imageCache.current.get(logoUrl);
            if (!cachedImage) {
                const img = document.createElement('img');
                img.crossOrigin = 'anonymous';
                img.src = logoUrl;
                // Gérer les erreurs de chargement
                img.onerror = () => {
                    debug_debugError('❌ [LOGO] Image failed to load:', logoUrl);
                };
                // ✅ CRITICAL: Quand l'image se charge, redessiner le canvas
                img.onload = () => {
                    const size = estimateImageMemorySize(img);
                    imageCache.current.set(logoUrl, {
                        image: img,
                        size: size,
                        lastUsed: Date.now()
                    });
                    // Déclencher un nettoyage après ajout
                    cleanupImageCache();
                    // Incrémenter le counter pour forcer un redraw
                    setImageLoadCount(prev => prev + 1);
                };
                // Retourner temporairement pour éviter les erreurs
                return;
            }
            const img = cachedImage.image;
            // Mettre à jour la date d'utilisation
            cachedImage.lastUsed = Date.now();
            // ✅ APPROCHE PLUS DIRECTE: Vérifier img.complete au rendu au lieu de compter sur onload
            // Rendre l'image si elle a une URL valide, même si elle n'est pas encore complètement chargée
            const shouldRenderImage = logoUrl && logoUrl.trim() !== '';
            // DEBUG: Log detailed breakdown of shouldRenderImage condition
            // DEBUG: Log image state with more details
            if (shouldRenderImage) {
                try {
                    // Appliquer la rotation si définie
                    const rotation = element.rotation || 0;
                    const opacity = element.opacity !== undefined ? element.opacity : 1;
                    const borderRadius = element.borderRadius || 0;
                    const objectFit = element.objectFit || 'contain';
                    // Calculer les dimensions et position selon objectFit
                    const containerWidth = element.width - 20;
                    const containerHeight = element.height - 20;
                    // Si l'image n'est pas encore chargée, utiliser des dimensions par défaut ou essayer de deviner
                    let imageAspectRatio;
                    if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                        imageAspectRatio = img.naturalWidth / img.naturalHeight;
                    }
                    else {
                        // Estimation par défaut pour les logos d'entreprise (généralement rectangulaires)
                        imageAspectRatio = 2; // 2:1 ratio par défaut
                    }
                    const containerAspectRatio = containerWidth / containerHeight;
                    let logoWidth;
                    let logoHeight;
                    let offsetX = 0;
                    let offsetY = 0;
                    switch (objectFit) {
                        case 'contain':
                            // Respecte les proportions, image tient entièrement dans le conteneur
                            if (containerAspectRatio > imageAspectRatio) {
                                logoHeight = containerHeight;
                                logoWidth = logoHeight * imageAspectRatio;
                            }
                            else {
                                logoWidth = containerWidth;
                                logoHeight = logoWidth / imageAspectRatio;
                            }
                            break;
                        case 'cover':
                            // Respecte les proportions, image couvre entièrement le conteneur
                            if (containerAspectRatio > imageAspectRatio) {
                                logoWidth = containerWidth;
                                logoHeight = logoWidth / imageAspectRatio;
                                offsetY = (containerHeight - logoHeight) / 2;
                            }
                            else {
                                logoHeight = containerHeight;
                                logoWidth = logoHeight * imageAspectRatio;
                                offsetX = (containerWidth - logoWidth) / 2;
                            }
                            break;
                        case 'fill':
                            // Étire l'image pour remplir exactement le conteneur
                            logoWidth = containerWidth;
                            logoHeight = containerHeight;
                            break;
                        case 'none':
                            // Taille originale, centrée
                            if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                                logoWidth = img.naturalWidth;
                                logoHeight = img.naturalHeight;
                            }
                            else {
                                // Taille par défaut si pas encore chargée
                                logoWidth = Math.min(containerWidth, 120);
                                logoHeight = Math.min(containerHeight, 60);
                            }
                            break;
                        case 'scale-down': {
                            // Taille originale ou contain, selon ce qui est plus petit
                            const originalWidth = img.naturalWidth || 120; // Défaut si pas chargé
                            const originalHeight = img.naturalHeight || 60; // Défaut si pas chargé
                            if (originalWidth <= containerWidth && originalHeight <= containerHeight) {
                                // Taille originale tient, l'utiliser
                                logoWidth = originalWidth;
                                logoHeight = originalHeight;
                            }
                            else {
                                // Utiliser contain
                                if (containerAspectRatio > imageAspectRatio) {
                                    logoHeight = containerHeight;
                                    logoWidth = logoHeight * imageAspectRatio;
                                }
                                else {
                                    logoWidth = containerWidth;
                                    logoHeight = logoWidth / imageAspectRatio;
                                }
                            }
                            break;
                        }
                        default:
                            // Par défaut contain
                            if (containerAspectRatio > imageAspectRatio) {
                                logoHeight = containerHeight;
                                logoWidth = logoHeight * imageAspectRatio;
                            }
                            else {
                                logoWidth = containerWidth;
                                logoHeight = logoWidth / imageAspectRatio;
                            }
                    }
                    // Calculer la position de base selon l'alignement
                    let x = 10;
                    if (alignment === 'center') {
                        x = (element.width - containerWidth) / 2;
                    }
                    else if (alignment === 'right') {
                        x = element.width - containerWidth - 10;
                    }
                    const y = (element.height - containerHeight) / 2;
                    // Ajuster pour centrer l'image dans son conteneur selon objectFit
                    const imageX = x + (containerWidth - logoWidth) / 2 + offsetX;
                    const imageY = y + (containerHeight - logoHeight) / 2 + offsetY;
                    // Sauvegarder le contexte
                    ctx.save();
                    // Appliquer l'opacité
                    if (opacity < 1) {
                        ctx.globalAlpha = opacity;
                    }
                    // Appliquer la rotation
                    if (rotation !== 0) {
                        const centerX = x + logoWidth / 2;
                        const centerY = y + logoHeight / 2;
                        ctx.translate(centerX, centerY);
                        ctx.rotate((rotation * Math.PI) / 180);
                        ctx.translate(-centerX, -centerY);
                    }
                    // Si borderRadius > 0, créer un chemin arrondi
                    if (borderRadius > 0) {
                        ctx.beginPath();
                        roundedRect(ctx, x, y, logoWidth, logoHeight, borderRadius);
                        ctx.clip();
                    }
                    // Essayer de dessiner l'image - si elle n'est pas chargée, cela ne fera rien
                    // mais au moins on aura essayé
                    ctx.drawImage(img, imageX, imageY, logoWidth, logoHeight);
                    // Restaurer le contexte
                    ctx.restore();
                }
                catch (error) {
                    debug_debugError(`❌ [LOGO] Error rendering image ${logoUrl}:`, error);
                    // En cas d'erreur, dessiner un placeholder
                    drawLogoPlaceholder(ctx, element, alignment, 'Erreur de chargement');
                }
            }
            else {
                // Pas d'URL valide, dessiner un placeholder
                drawLogoPlaceholder(ctx, element, alignment, 'URL manquante');
            }
        }
        else {
            // Pas d'URL, dessiner un placeholder
            drawLogoPlaceholder(ctx, element, alignment, 'Company_logo');
        }
    }, [drawLogoPlaceholder, cleanupImageCache, estimateImageMemorySize]); // ✅ BUGFIX-008: REMOVED setImageLoadCounter
    // ✅ BUGFIX-007: Memoize drawDynamicText to prevent recreation on every render
    const drawDynamicText = (0,react.useCallback)((ctx, element) => {
        const props = element;
        const text = props.text || 'Texte personnalisable';
        const fontSize = props.fontSize || 14;
        const fontFamily = props.fontFamily || 'Arial';
        const fontWeight = props.fontWeight || 'normal';
        const fontStyle = props.fontStyle || 'normal';
        const autoWrap = props.autoWrap !== false; // Par défaut activé
        // Appliquer le fond seulement si showBackground est activé
        if (props.showBackground !== false) {
            ctx.fillStyle = props.backgroundColor || '#e5e7eb';
            ctx.fillRect(0, 0, element.width, element.height);
        }
        ctx.fillStyle = '#000000';
        ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
        ctx.textAlign = 'left';
        // Remplacer les variables génériques par des valeurs par défaut
        const processedText = text
            .replace(/\[date\]/g, new Date().toLocaleDateString('fr-FR'))
            .replace(/\[nom\]/g, 'Dupont')
            .replace(/\[prenom\]/g, 'Marie')
            .replace(/\[entreprise\]/g, 'Ma Société')
            .replace(/\[telephone\]/g, '+33 1 23 45 67 89')
            .replace(/\[email\]/g, 'contact@masociete.com')
            .replace(/\[site\]/g, 'www.masociete.com')
            .replace(/\[ville\]/g, 'Paris')
            .replace(/\[siret\]/g, '123 456 789 00012')
            .replace(/\[tva\]/g, 'FR 12 345 678 901')
            .replace(/\[capital\]/g, '10 000')
            .replace(/\[rcs\]/g, 'Paris B 123 456 789');
        if (autoWrap) {
            // Fonction pour diviser le texte en lignes selon la largeur disponible
            const wrapText = (text, maxWidth) => {
                const words = text.split(' ');
                const lines = [];
                let currentLine = '';
                for (const word of words) {
                    const testLine = currentLine + (currentLine ? ' ' : '') + word;
                    const metrics = ctx.measureText(testLine);
                    if (metrics.width > maxWidth && currentLine) {
                        lines.push(currentLine);
                        currentLine = word;
                    }
                    else {
                        currentLine = testLine;
                    }
                }
                if (currentLine) {
                    lines.push(currentLine);
                }
                return lines;
            };
            // Gérer les sauts de ligne existants (\n)
            const paragraphs = processedText.split('\n');
            let y = 25;
            paragraphs.forEach((paragraph) => {
                if (paragraph.trim()) {
                    const lines = wrapText(paragraph, element.width - 20); // Marge de 10px de chaque côté
                    lines.forEach((line) => {
                        ctx.fillText(line, 10, y);
                        y += fontSize + 4; // Espacement entre lignes
                    });
                }
                else {
                    y += fontSize + 4; // Ligne vide
                }
            });
        }
        else {
            // Comportement original : gérer uniquement les \n existants
            const lines = processedText.split('\n');
            let y = 25;
            lines.forEach((line) => {
                ctx.fillText(line, 10, y);
                y += fontSize + 4;
            });
        }
    }, []); // No deps - pure function
    // ✅ BUGFIX-007: Memoize drawMentions to prevent recreation on every render
    const drawMentions = (0,react.useCallback)((ctx, element) => {
        const props = element;
        const fontSizeRaw = props.fontSize || 10;
        // ✅ BUGFIX-021: Robust font size parsing for various formats
        let fontSize;
        if (typeof fontSizeRaw === 'number') {
            fontSize = fontSizeRaw;
        }
        else if (typeof fontSizeRaw === 'string') {
            // Try removing 'px', 'em', 'rem', 'pt' suffixes
            const numStr = fontSizeRaw.replace(/px|em|rem|pt|%/g, '').trim();
            fontSize = parseFloat(numStr) || 10;
            // If it's 'em' or 'rem', convert to approximate px (1em ≈ 16px)
            if (fontSizeRaw.includes('em') || fontSizeRaw.includes('rem')) {
                fontSize = fontSize * 16;
            }
        }
        else {
            fontSize = 10;
        }
        // Ensure fontSize is reasonable
        fontSize = Math.max(6, Math.min(72, fontSize));
        const fontFamily = props.fontFamily || 'Arial';
        const fontWeight = props.fontWeight || 'normal';
        const fontStyle = props.fontStyle || 'normal';
        const textAlign = props.textAlign || 'left';
        const text = props.text || 'SARL au capital de 10 000€ - RCS Lyon 123 456 789\nTVA FR 12 345 678 901 - SIRET 123 456 789 00012\ncontact@maboutique.com - +33 4 12 34 56 78';
        const showSeparator = props.showSeparator !== false;
        const separatorStyle = props.separatorStyle || 'solid';
        const theme = (props.theme || 'legal');
        // Définition des thèmes pour les mentions
        const themes = {
            legal: {
                backgroundColor: '#ffffff',
                borderColor: '#6b7280',
                textColor: '#374151',
                headerTextColor: '#111827'
            },
            subtle: {
                backgroundColor: '#f9fafb',
                borderColor: '#e5e7eb',
                textColor: '#6b7280',
                headerTextColor: '#374151'
            },
            minimal: {
                backgroundColor: '#ffffff',
                borderColor: '#f3f4f6',
                textColor: '#9ca3af',
                headerTextColor: '#6b7280'
            }
        };
        const currentTheme = themes[theme] || themes.legal;
        // Utiliser les couleurs personnalisées si définies, sinon utiliser le thème
        const bgColor = normalizeColor(props.backgroundColor || currentTheme.backgroundColor);
        const txtColor = normalizeColor(props.textColor || currentTheme.textColor);
        // Appliquer le fond seulement si showBackground est activé
        if (props.showBackground !== false) {
            ctx.fillStyle = bgColor;
            ctx.fillRect(0, 0, element.width, element.height);
        }
        ctx.fillStyle = txtColor;
        let y = 15;
        // Dessiner le séparateur si activé
        if (showSeparator) {
            ctx.strokeStyle = txtColor;
            ctx.lineWidth = 1;
            if (separatorStyle === 'double') {
                ctx.beginPath();
                ctx.moveTo(10, y - 5);
                ctx.lineTo(element.width - 10, y - 5);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(10, y - 2);
                ctx.lineTo(element.width - 10, y - 2);
                ctx.stroke();
            }
            else {
                ctx.setLineDash(separatorStyle === 'dashed' ? [5, 5] : separatorStyle === 'dotted' ? [2, 2] : []);
                ctx.beginPath();
                ctx.moveTo(10, y - 5);
                ctx.lineTo(element.width - 10, y - 5);
                ctx.stroke();
                ctx.setLineDash([]); // Reset line dash
            }
            y += 10; // Espace après le séparateur
        }
        ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
        ctx.textAlign = textAlign;
        // Fonction de wrapping du texte
        const wrapText = (text, maxWidth) => {
            if (!text)
                return [''];
            // Traiter chaque paragraphe séparément (séparé par \n)
            const paragraphs = text.split('\n');
            const wrappedParagraphs = [];
            for (const paragraph of paragraphs) {
                if (paragraph.trim() === '') {
                    // Ligne vide (séparateur), on la garde telle quelle
                    wrappedParagraphs.push('');
                    continue;
                }
                // Wrapper le paragraphe comme avant
                const words = paragraph.split(' ');
                const lines = [];
                let currentLine = '';
                for (const word of words) {
                    const testLine = currentLine ? currentLine + ' ' + word : word;
                    const metrics = ctx.measureText(testLine);
                    if (metrics.width > maxWidth && currentLine) {
                        // Le mot ne rentre pas, on passe à la ligne
                        lines.push(currentLine);
                        currentLine = word;
                    }
                    else {
                        currentLine = testLine;
                    }
                }
                if (currentLine) {
                    lines.push(currentLine);
                }
                wrappedParagraphs.push(...lines);
            }
            return wrappedParagraphs;
        };
        // Wrapper le texte selon la largeur disponible
        const maxWidth = element.width - 20; // Marge de 20px
        const wrappedLines = wrapText(text, maxWidth);
        // Calculer le nombre maximum de lignes qui peuvent tenir
        const lineHeight = fontSize + 2;
        const maxLines = Math.floor((element.height - (showSeparator ? 25 : 15)) / lineHeight);
        // Rendre seulement les lignes qui tiennent
        wrappedLines.slice(0, maxLines).forEach((line, index) => {
            const x = textAlign === 'center' ? element.width / 2 : textAlign === 'right' ? element.width - 10 : 10;
            const lineY = (showSeparator ? 25 : 15) + index * lineHeight;
            ctx.fillText(line, x, lineY);
        });
    }, []); // No deps - pure function
    // ✅ BUGFIX-001/004: Memoize drawElement but pass state as parameter to avoid dependency cycle
    const drawElement = (0,react.useCallback)((ctx, element, currentState) => {
        // Vérifier si l'élément est visible
        if (element.visible === false) {
            debugLog(`[Canvas] Skipping invisible element: ${element.type} (${element.id})`);
            return;
        }
        debugLog(`[Canvas] Drawing element: ${element.type} (${element.id}) - Position: (${element.x}, ${element.y}), Size: ${element.width}x${element.height}, Rotation: ${element.rotation || 0}°`);
        ctx.save();
        // Appliquer transformation de l'élément
        if (element.rotation) {
            // Rotation autour du centre de l'élément
            const centerX = element.width / 2;
            const centerY = element.height / 2;
            ctx.translate(element.x + centerX, element.y + centerY);
            ctx.rotate((element.rotation * Math.PI) / 180);
            ctx.translate(-centerX, -centerY);
        }
        else {
            // Pas de rotation, translation normale
            ctx.translate(element.x, element.y);
        }
        // Dessiner selon le type d'élément
        switch (element.type) {
            case 'rectangle':
                debugLog(`[Canvas] Rendering rectangle element: ${element.id}`);
                drawRectangle(ctx, element);
                break;
            case 'circle':
                debugLog(`[Canvas] Rendering circle element: ${element.id}`);
                drawCircle(ctx, element);
                break;
            case 'text':
                debugLog(`[Canvas] Rendering text element: ${element.id}`);
                drawText(ctx, element);
                break;
            case 'line':
                debugLog(`[Canvas] Rendering line element: ${element.id}`);
                drawLine(ctx, element);
                break;
            case 'product_table':
                debugLog(`[Canvas] Rendering product table element: ${element.id}`);
                drawProductTable(ctx, element, currentState);
                break;
            case 'customer_info':
                debugLog(`[Canvas] Rendering customer info element: ${element.id}`);
                drawCustomerInfo(ctx, element, currentState);
                break;
            case 'company_info':
                debugLog(`[Canvas] Rendering company info element: ${element.id}`);
                drawCompanyInfo(ctx, element, canvasSettings);
                break;
            case 'company_logo':
                debugLog(`[Canvas] Rendering company logo element: ${element.id}`);
                drawCompanyLogo(ctx, element);
                break;
            case 'order-number':
            case 'order_number':
                debugLog(`[Canvas] Rendering order number element: ${element.id}`);
                drawOrderNumber(ctx, element, currentState);
                break;
            case 'document_type':
                debugLog(`[Canvas] Rendering document type element: ${element.id}`);
                drawDocumentType(ctx, element, currentState);
                break;
            case 'dynamic-text':
                debugLog(`[Canvas] Rendering dynamic text element: ${element.id}`);
                drawDynamicText(ctx, element);
                break;
            case 'mentions':
                debugLog(`[Canvas] Rendering mentions element: ${element.id}`);
                drawMentions(ctx, element);
                break;
            case 'image':
                debugLog(`[Canvas] Rendering image element: ${element.id}`);
                drawImage(ctx, element, imageCache);
                break;
            default:
                debugWarn(`[Canvas] Unknown element type: ${element.type} for element ${element.id}`);
                // Élément générique - dessiner un rectangle simple
                ctx.strokeStyle = '#000000';
                ctx.lineWidth = 1;
                ctx.strokeRect(0, 0, element.width, element.height);
        }
        ctx.restore();
    }, [drawCompanyLogo, drawDynamicText, drawMentions, canvasSettings]); // ✅ BUGFIX-007: Include memoized draw functions
    // Fonction pour dessiner la sélection
    const drawSelection = (0,react.useCallback)((ctx, selectedIds, elements) => {
        const selectedElements = elements.filter(el => selectedIds.includes(el.id));
        if (selectedElements.length === 0) {
            debugLog('[Canvas] Selection cleared - no elements selected');
            return;
        }
        debugLog(`[Canvas] Drawing selection for ${selectedElements.length} element(s):`, selectedIds);
        // Calculer les bounds de sélection
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        selectedElements.forEach(el => {
            minX = Math.min(minX, el.x);
            minY = Math.min(minY, el.y);
            maxX = Math.max(maxX, el.x + el.width);
            maxY = Math.max(maxY, el.y + el.height);
        });
        // Rectangle de sélection
        ctx.strokeStyle = '#007acc';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        ctx.strokeRect(minX - 2, minY - 2, maxX - minX + 4, maxY - minY + 4);
        // Poignées de redimensionnement (conditionnées par les settings)
        if (canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.selectionShowHandles) {
            const handleSize = 6;
            ctx.fillStyle = '#007acc';
            ctx.setLineDash([]);
            // Coins
            ctx.fillRect(minX - handleSize / 2, minY - handleSize / 2, handleSize, handleSize);
            ctx.fillRect(maxX - handleSize / 2, minY - handleSize / 2, handleSize, handleSize);
            ctx.fillRect(minX - handleSize / 2, maxY - handleSize / 2, handleSize, handleSize);
            ctx.fillRect(maxX - handleSize / 2, maxY - handleSize / 2, handleSize, handleSize);
            // Centres des côtés
            const midX = (minX + maxX) / 2;
            const midY = (minY + maxY) / 2;
            ctx.fillRect(midX - handleSize / 2, minY - handleSize / 2, handleSize, handleSize);
            ctx.fillRect(midX - handleSize / 2, maxY - handleSize / 2, handleSize, handleSize);
            ctx.fillRect(minX - handleSize / 2, midY - handleSize / 2, handleSize, handleSize);
            ctx.fillRect(maxX - handleSize / 2, midY - handleSize / 2, handleSize, handleSize);
        }
        // Poignées de rotation (conditionnées par les settings)
        if (canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.selectionRotationEnabled) {
            const rotationHandleSize = 8;
            const rotationHandleDistance = 20;
            // Vérifier si au moins un élément a une rotation proche de 0°
            // Utiliser la même logique de normalisation que dans useCanvasInteraction.ts
            const hasZeroRotation = selectedElements.some(el => {
                const rotation = el.rotation || 0;
                // Normaliser l'angle entre -180° et 180° (même logique que le snap)
                let normalizedRotation = rotation % 360;
                if (normalizedRotation > 180)
                    normalizedRotation -= 360;
                if (normalizedRotation < -180)
                    normalizedRotation += 360;
                // Utiliser la tolérance pour 0° (10°) pour cohérence avec le snap ultra simple
                return Math.abs(normalizedRotation - 0) <= 10;
            });
            // Couleur différente pour indiquer le snap à 0°
            const handleColor = hasZeroRotation ? '#00cc44' : '#007acc';
            ctx.fillStyle = handleColor;
            ctx.strokeStyle = handleColor;
            ctx.lineWidth = 2;
            ctx.setLineDash([]);
            // Centre de la sélection
            const centerX = (minX + maxX) / 2;
            const centerY = (minY + maxY) / 2;
            // Position de la poignée de rotation (au-dessus du centre)
            const rotationHandleX = centerX;
            const rotationHandleY = minY - rotationHandleDistance;
            // Cercle pour la poignée de rotation
            ctx.beginPath();
            ctx.arc(rotationHandleX, rotationHandleY, rotationHandleSize / 2, 0, 2 * Math.PI);
            ctx.fill();
            // Ligne reliant la poignée au centre
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.lineTo(rotationHandleX, rotationHandleY);
            ctx.stroke();
        }
        // Afficher les dimensions pour chaque élément sélectionné
        selectedElements.forEach(el => {
            if (selectedIds.includes(el.id)) {
                // Coordonnées
                const x = el.x;
                const y = el.y;
                const width = el.width;
                const height = el.height;
                // Afficher les dimensions en pixels sur le coin supérieur droit
                ctx.font = '11px Arial';
                ctx.fillStyle = '#007acc';
                ctx.textAlign = 'right';
                ctx.textBaseline = 'top';
                const dimensionText = `${(width * 1).toFixed(1)}×${(height * 1).toFixed(1)}px`;
                const padding = 4;
                const textWidth = ctx.measureText(dimensionText).width;
                // Fond blanc pour meilleure lisibilité
                ctx.fillStyle = 'white';
                ctx.fillRect(x + width - textWidth - padding * 2, y - 20, textWidth + padding * 2, 18);
                // Texte
                ctx.fillStyle = '#007acc';
                ctx.font = 'bold 11px Arial';
                ctx.fillText(dimensionText, x + width - padding, y - 16);
            }
        });
    }, [canvasSettings]);
    // Fonctions pour gérer le menu contextuel
    const showContextMenu = (0,react.useCallback)((x, y, elementId) => {
        setContextMenu({
            isVisible: true,
            position: { x, y },
            elementId
        });
    }, []);
    const hideContextMenu = (0,react.useCallback)(() => {
        setContextMenu(prev => ({ ...prev, isVisible: false }));
    }, []);
    const handleContextMenuAction = (0,react.useCallback)((action, elementId) => {
        debugLog(`[Canvas] Context menu action: ${action} on element ${elementId || 'none'}`);
        if (!elementId)
            return;
        switch (action) {
            case 'bring-to-front': {
                debugLog(`[Canvas] Bringing element ${elementId} to front`);
                // Déplacer l'élément à la fin du tableau (devant tous les autres)
                const elementIndex = state.elements.findIndex(el => el.id === elementId);
                if (elementIndex !== -1) {
                    const element = state.elements[elementIndex];
                    const newElements = [
                        ...state.elements.slice(0, elementIndex),
                        ...state.elements.slice(elementIndex + 1),
                        element
                    ];
                    dispatch({ type: 'SET_ELEMENTS', payload: newElements });
                }
                break;
            }
            case 'send-to-back': {
                debugLog(`[Canvas] Sending element ${elementId} to back`);
                // Déplacer l'élément au début du tableau (derrière tous les autres)
                const elementIndex = state.elements.findIndex(el => el.id === elementId);
                if (elementIndex !== -1) {
                    const element = state.elements[elementIndex];
                    const newElements = [
                        element,
                        ...state.elements.slice(0, elementIndex),
                        ...state.elements.slice(elementIndex + 1)
                    ];
                    dispatch({ type: 'SET_ELEMENTS', payload: newElements });
                }
                break;
            }
            case 'bring-forward': {
                debugLog(`[Canvas] Bringing element ${elementId} forward`);
                // Déplacer l'élément d'une position vers l'avant
                const elementIndex = state.elements.findIndex(el => el.id === elementId);
                if (elementIndex !== -1 && elementIndex < state.elements.length - 1) {
                    const newElements = [...state.elements];
                    [newElements[elementIndex], newElements[elementIndex + 1]] =
                        [newElements[elementIndex + 1], newElements[elementIndex]];
                    dispatch({ type: 'SET_ELEMENTS', payload: newElements });
                }
                break;
            }
            case 'send-backward': {
                debugLog(`[Canvas] Sending element ${elementId} backward`);
                // Déplacer l'élément d'une position vers l'arrière
                const elementIndex = state.elements.findIndex(el => el.id === elementId);
                if (elementIndex > 0) {
                    const newElements = [...state.elements];
                    [newElements[elementIndex], newElements[elementIndex - 1]] =
                        [newElements[elementIndex - 1], newElements[elementIndex]];
                    dispatch({ type: 'SET_ELEMENTS', payload: newElements });
                }
                break;
            }
            case 'duplicate': {
                debugLog(`[Canvas] Duplicating element ${elementId}`);
                // Dupliquer l'élément avec un nouvel ID et un léger décalage
                const element = state.elements.find(el => el.id === elementId);
                if (element) {
                    const duplicatedElement = {
                        ...element,
                        id: `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                        x: element.x + 10,
                        y: element.y + 10,
                        createdAt: new Date(),
                        updatedAt: new Date()
                    };
                    dispatch({ type: 'ADD_ELEMENT', payload: duplicatedElement });
                }
                break;
            }
            case 'copy': {
                debugLog(`[Canvas] Copying element ${elementId}`);
                // Copier l'élément dans le presse-papiers interne
                const element = state.elements.find(el => el.id === elementId);
                if (element) {
                    // TODO: Implémenter le presse-papiers interne
                }
                break;
            }
            case 'cut': {
                debugLog(`[Canvas] Cutting element ${elementId}`);
                // Couper l'élément (copier puis supprimer)
                const element = state.elements.find(el => el.id === elementId);
                if (element) {
                    // TODO: Implémenter le presse-papiers interne
                    // dispatch({ type: 'REMOVE_ELEMENT', payload: elementId });
                }
                break;
            }
            case 'reset-size': {
                debugLog(`[Canvas] Resetting size for element ${elementId}`);
                // Réinitialiser la taille de l'élément à ses dimensions par défaut
                const element = state.elements.find(el => el.id === elementId);
                if (element) {
                    const defaultSizes = {
                        rectangle: { width: 100, height: 100 },
                        circle: { width: 100, height: 100 },
                        text: { width: 100, height: 30 },
                        line: { width: 100, height: 2 },
                        product_table: { width: 400, height: 200 },
                        customer_info: { width: 300, height: 80 },
                        company_info: { width: 300, height: 120 },
                        company_logo: { width: 150, height: 80 },
                        'order-number': { width: 200, height: 40 },
                        document_type: { width: 150, height: 30 },
                        'dynamic-text': { width: 200, height: 60 },
                        mentions: { width: 400, height: 80 }
                    };
                    const defaultSize = defaultSizes[element.type] || { width: 100, height: 100 };
                    dispatch({
                        type: 'UPDATE_ELEMENT',
                        payload: {
                            id: elementId,
                            updates: { width: defaultSize.width, height: defaultSize.height }
                        }
                    });
                }
                break;
            }
            case 'fit-to-content': {
                debugLog(`[Canvas] Fitting element ${elementId} to content`);
                // Ajuster la taille de l'élément à son contenu (pour le texte principalement)
                const element = state.elements.find(el => el.id === elementId);
                if (element && (element.type === 'text' || element.type === 'dynamic-text')) {
                    // Pour les éléments texte, ajuster la hauteur selon le contenu
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    if (ctx) {
                        const props = element;
                        const fontSize = props.fontSize || 14;
                        const fontFamily = props.fontFamily || 'Arial';
                        const fontWeight = props.fontWeight || 'normal';
                        const fontStyle = props.fontStyle || 'normal';
                        ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
                        const text = props.text || 'Texte';
                        const lines = text.split('\n');
                        const lineHeight = fontSize + 4;
                        const contentHeight = lines.length * lineHeight + 20; // Marges
                        dispatch({
                            type: 'UPDATE_ELEMENT',
                            payload: {
                                id: elementId,
                                updates: { height: Math.max(contentHeight, 30) }
                            }
                        });
                    }
                }
                break;
            }
            case 'delete':
                debugLog(`[Canvas] Deleting element ${elementId}`);
                dispatch({ type: 'REMOVE_ELEMENT', payload: elementId });
                break;
            case 'lock': {
                debugLog(`[Canvas] Toggling lock for element ${elementId}`);
                // Basculer l'état verrouillé de l'élément
                const element = state.elements.find(el => el.id === elementId);
                if (element) {
                    dispatch({
                        type: 'UPDATE_ELEMENT',
                        payload: {
                            id: elementId,
                            updates: { locked: !element.locked }
                        }
                    });
                }
                break;
            }
        }
    }, [state.elements, dispatch]);
    const getContextMenuItems = (0,react.useCallback)((elementId) => {
        if (!elementId) {
            // Menu contextuel pour le canvas vide
            return [
                {
                    id: 'section-edit',
                    section: 'ÉDITION'
                },
                {
                    id: 'paste',
                    label: 'Coller',
                    icon: '📋',
                    shortcut: 'Ctrl+V',
                    action: () => {
                        // TODO: Implémenter le collage depuis le presse-papiers
                    },
                    disabled: true // Désactiver jusqu'à implémentation
                },
                {
                    id: 'select-all',
                    label: 'Tout sélectionner',
                    icon: '☑️',
                    shortcut: 'Ctrl+A',
                    action: () => {
                        // Sélectionner tous les éléments
                        const allElementIds = state.elements.map(el => el.id);
                        dispatch({ type: 'SET_SELECTION', payload: allElementIds });
                    }
                }
            ];
        }
        // Menu contextuel pour un élément
        const element = state.elements.find(el => el.id === elementId);
        const isLocked = (element === null || element === void 0 ? void 0 : element.locked) || false;
        const items = [
            // Section Ordre des calques
            {
                id: 'section-layers',
                section: 'CALQUES'
            },
            {
                id: 'layer-order',
                label: 'Ordre des calques',
                icon: '📚',
                children: [
                    {
                        id: 'bring-to-front',
                        label: 'Premier plan',
                        icon: '⬆️',
                        shortcut: 'Ctrl+↑',
                        action: () => handleContextMenuAction('bring-to-front', elementId),
                        disabled: isLocked
                    },
                    {
                        id: 'send-to-back',
                        label: 'Arrière plan',
                        icon: '⬇️',
                        shortcut: 'Ctrl+↓',
                        action: () => handleContextMenuAction('send-to-back', elementId),
                        disabled: isLocked
                    },
                    {
                        id: 'bring-forward',
                        label: 'Avancer d\'un plan',
                        icon: '↗️',
                        shortcut: 'Ctrl+Shift+↑',
                        action: () => handleContextMenuAction('bring-forward', elementId),
                        disabled: isLocked
                    },
                    {
                        id: 'send-backward',
                        label: 'Reculer d\'un plan',
                        icon: '↙️',
                        shortcut: 'Ctrl+Shift+↓',
                        action: () => handleContextMenuAction('send-backward', elementId),
                        disabled: isLocked
                    }
                ]
            },
            { id: 'separator1', separator: true },
            // Section Édition
            {
                id: 'section-edit',
                section: 'ÉDITION'
            },
            {
                id: 'duplicate',
                label: 'Dupliquer',
                icon: '📋',
                shortcut: 'Ctrl+D',
                action: () => handleContextMenuAction('duplicate', elementId),
                disabled: isLocked,
                children: [
                    {
                        id: 'duplicate-here',
                        label: 'Dupliquer ici',
                        icon: '📋',
                        action: () => handleContextMenuAction('duplicate', elementId),
                        disabled: isLocked
                    },
                    {
                        id: 'duplicate-multiple',
                        label: 'Dupliquer plusieurs...',
                        icon: '📋📋',
                        action: () => handleContextMenuAction('duplicate-multiple', elementId),
                        disabled: isLocked
                    }
                ]
            },
            {
                id: 'clipboard',
                label: 'Presse-papiers',
                icon: '📄',
                children: [
                    {
                        id: 'copy',
                        label: 'Copier',
                        icon: '📄',
                        shortcut: 'Ctrl+C',
                        action: () => handleContextMenuAction('copy', elementId),
                        disabled: false
                    },
                    {
                        id: 'cut',
                        label: 'Couper',
                        icon: '✂️',
                        shortcut: 'Ctrl+X',
                        action: () => handleContextMenuAction('cut', elementId),
                        disabled: isLocked
                    }
                ]
            },
            { id: 'separator2', separator: true },
            // Section Taille
            {
                id: 'section-size',
                section: 'TAILLE'
            },
            {
                id: 'reset-size',
                label: 'Taille par défaut',
                icon: '📏',
                shortcut: 'Ctrl+0',
                action: () => handleContextMenuAction('reset-size', elementId),
                disabled: isLocked
            },
            {
                id: 'fit-to-content',
                label: 'Ajuster au contenu',
                icon: '📐',
                shortcut: 'Ctrl+Shift+F',
                action: () => handleContextMenuAction('fit-to-content', elementId),
                disabled: isLocked || !((element === null || element === void 0 ? void 0 : element.type) === 'text' || (element === null || element === void 0 ? void 0 : element.type) === 'dynamic-text')
            },
            { id: 'separator3', separator: true },
            // Section État
            {
                id: 'section-state',
                section: 'ÉTAT'
            },
            {
                id: 'lock',
                label: isLocked ? 'Déverrouiller' : 'Verrouiller',
                icon: isLocked ? '🔓' : '🔒',
                shortcut: isLocked ? 'Ctrl+Shift+L' : 'Ctrl+L',
                action: () => handleContextMenuAction('lock', elementId)
            },
            { id: 'separator4', separator: true },
            // Section Danger
            {
                id: 'section-danger',
                section: 'SUPPRESSION'
            },
            {
                id: 'delete',
                label: 'Supprimer',
                icon: '🗑️',
                shortcut: 'Suppr',
                action: () => handleContextMenuAction('delete', elementId),
                disabled: false
            }
        ];
        return items;
    }, [state.elements, handleContextMenuAction, dispatch]);
    // Fonction pour dessiner la grille
    const drawGrid = (0,react.useCallback)((ctx, w, h, size, color) => {
        ctx.strokeStyle = color;
        ctx.lineWidth = 1;
        for (let x = 0; x <= w; x += size) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        for (let y = 0; y <= h; y += size) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }
    }, []); // No deps - pure function
    // Fonction pour dessiner les guides
    const drawGuides = (0,react.useCallback)((ctx, canvasWidth, canvasHeight) => {
        ctx.save();
        ctx.strokeStyle = '#007acc';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        // Guide horizontal au milieu
        ctx.beginPath();
        ctx.moveTo(0, canvasHeight / 2);
        ctx.lineTo(canvasWidth, canvasHeight / 2);
        ctx.stroke();
        // Guide vertical au milieu
        ctx.beginPath();
        ctx.moveTo(canvasWidth / 2, 0);
        ctx.lineTo(canvasWidth / 2, canvasHeight);
        ctx.stroke();
        ctx.restore();
    }, []);
    // Gestionnaire de clic droit pour le canvas
    const handleCanvasContextMenu = (0,react.useCallback)((event) => {
        event.preventDefault();
        debugLog(`👆 Canvas: Context menu triggered at (${event.clientX}, ${event.clientY})`);
        debugLog(`[Canvas] Context menu triggered at (${event.clientX}, ${event.clientY})`);
        handleContextMenu(event, (x, y, elementId) => {
            debugLog(`📋 Canvas: Context menu callback - Element: ${elementId || 'canvas'}, Position: (${x}, ${y})`);
            debugLog(`[Canvas] Context menu callback - Element: ${elementId || 'canvas'}, Position: (${x}, ${y})`);
            showContextMenu(x, y, elementId);
        });
    }, [handleContextMenu, showContextMenu]);
    // Fonction de rendu du canvas
    const renderCanvas = (0,react.useCallback)(() => {
        const startTime = Date.now();
        renderCountRef.current += 1;
        debugLog(`🎨 Canvas: Render #${renderCountRef.current} started - Elements: ${state.elements.length}, Zoom: ${state.canvas.zoom}%, Selection: ${state.selection.selectedElements.length} items`);
        debugLog(`[Canvas] Render #${renderCountRef.current} started - Elements: ${state.elements.length}, Zoom: ${state.canvas.zoom}%, Pan: (${state.canvas.pan.x.toFixed(1)}, ${state.canvas.pan.y.toFixed(1)}), Selection: ${state.selection.selectedElements.length} items`);
        const canvas = canvasRef.current;
        if (!canvas) {
            debugLog('❌ Canvas: Render cancelled - canvas ref is null');
            debugLog('[Canvas] Render cancelled - canvas ref is null');
            return;
        }
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            debugLog('❌ Canvas: Render cancelled - canvas context unavailable');
            debugLog('[Canvas] Render cancelled - canvas context unavailable');
            return;
        }
        // Clear canvas with background color from settings (matching PDF background)
        const canvasBgColor = normalizeColor((canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.canvasBackgroundColor) || '#ffffff');
        debugLog(`🖌️ Canvas: Clearing canvas with background color: ${canvasBgColor}`);
        debugLog(`[Canvas] Clearing canvas with background color: ${canvasBgColor}`);
        ctx.fillStyle = canvasBgColor;
        ctx.fillRect(0, 0, width, height);
        // Note: Canvas border is now handled by CSS styling based on settings
        // DEBUG: Log elements
        if (state.elements.length === 0) {
            // Pas d'éléments à dessiner
        }
        else {
            // Éléments présents
        }
        // Appliquer transformation (pan uniquement - zoom géré par CSS)
        ctx.save();
        ctx.translate(state.canvas.pan.x, state.canvas.pan.y);
        // Note: Zoom is now handled by CSS display size, no need for ctx.scale()
        // NOTE: Les marges seront réactivées après que le rendu des éléments soit fixé
        // const showMargins = canvasSettings.showMargins;
        // if (showMargins && canvasSettings) {
        //   const marginTopPx = (canvasSettings.marginTop || 0) * 3.78;
        //   const marginLeftPx = (canvasSettings.marginLeft || 0) * 3.78;
        //   ctx.translate(marginLeftPx, marginTopPx);
        // }
        // Dessiner la grille si activée (utiliser les paramètres Canvas Settings et l'état du toggle)
        if ((canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.gridShow) && state.canvas.showGrid) {
            drawGrid(ctx, width, height, (canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.gridSize) || 20, (canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.gridColor) || '#e0e0e0');
        }
        // Dessiner les guides si activés (utiliser les paramètres Canvas Settings et l'état du template)
        if ((canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.guidesEnabled) && state.template.showGuides) {
            drawGuides(ctx, width, height);
        }
        // Dessiner les éléments
        debugLog(`📝 Canvas: Rendering ${visibleElementsList.length} visible elements (lazy loading: ${lazyLoadingEnabled})`);
        debugLog(`[Canvas] Rendering ${visibleElementsList.length} visible elements (lazy loading: ${lazyLoadingEnabled})`);
        visibleElementsList.forEach((element) => {
            debugLog(`🎯 Canvas: Drawing element: ${element.type} (${element.id}) at (${element.x}, ${element.y}) ${element.width}x${element.height}`);
            debugLog(`[Canvas] Drawing element: ${element.type} (${element.id}) at (${element.x}, ${element.y}) ${element.width}x${element.height}`);
            drawElement(ctx, element, state); // ✅ BUGFIX-001/004: Pass state as parameter
        });
        // Dessiner la sélection temporaire (rectangle/lasso en cours)
        if (selectionState === null || selectionState === void 0 ? void 0 : selectionState.isSelecting) {
            if (selectionState.selectionMode === 'rectangle' && selectionState.selectionRect.width > 0 && selectionState.selectionRect.height > 0) {
                // Dessiner le rectangle de sélection
                ctx.save();
                ctx.strokeStyle = '#0066cc';
                ctx.lineWidth = 1;
                ctx.setLineDash([5, 5]);
                ctx.strokeRect(selectionState.selectionRect.x, selectionState.selectionRect.y, selectionState.selectionRect.width, selectionState.selectionRect.height);
                // Remplir avec une couleur semi-transparente
                ctx.fillStyle = 'rgba(0, 102, 204, 0.1)';
                ctx.fillRect(selectionState.selectionRect.x, selectionState.selectionRect.y, selectionState.selectionRect.width, selectionState.selectionRect.height);
                ctx.restore();
            }
            else if (selectionState.selectionMode === 'lasso' && selectionState.selectionPoints.length > 1) {
                // Dessiner le lasso
                ctx.save();
                ctx.strokeStyle = '#0066cc';
                ctx.lineWidth = 1;
                ctx.setLineDash([5, 5]);
                ctx.beginPath();
                ctx.moveTo(selectionState.selectionPoints[0].x, selectionState.selectionPoints[0].y);
                for (let i = 1; i < selectionState.selectionPoints.length; i++) {
                    ctx.lineTo(selectionState.selectionPoints[i].x, selectionState.selectionPoints[i].y);
                }
                ctx.closePath();
                ctx.stroke();
                // Remplir avec une couleur semi-transparente
                ctx.fillStyle = 'rgba(0, 102, 204, 0.1)';
                ctx.fill();
                ctx.restore();
            }
        }
        // Dessiner la sélection
        if (state.selection.selectedElements.length > 0) {
            drawSelection(ctx, state.selection.selectedElements, state.elements);
        }
        ctx.restore();
        // Log rendu terminé avec métriques de performance
        const renderTime = Date.now() - startTime;
        debugLog(`✅ Canvas: Render #${renderCountRef.current} completed in ${renderTime}ms - ${state.elements.length} elements rendered`);
        debugLog(`[Canvas] Render #${renderCountRef.current} completed in ${renderTime}ms - ${state.elements.length} elements rendered`);
        // Log avertissement si le rendu prend trop de temps
        if (renderTime > 100) {
            debugWarn(`⚠️ Canvas: Slow render detected: ${renderTime}ms for ${state.elements.length} elements`);
            debugWarn(`[Canvas] Slow render detected: ${renderTime}ms for ${state.elements.length} elements`);
        }
    }, [width, height, canvasSettings, state, drawElement, drawGrid, drawGuides, selectionState, drawSelection, visibleElementsList]); // ✅ Include memoized drawGrid and drawGuides
    // Redessiner quand l'état change - CORRECTION: Supprimer renderCanvas des dépendances pour éviter les boucles
    (0,react.useEffect)(() => {
        debugLog(`🔄 Canvas: State change detected - triggering render. Elements: ${state.elements.length}, Selection: ${state.selection.selectedElements.length}, Zoom: ${state.canvas.zoom}%`);
        debugLog(`[Canvas] State change detected - triggering render. Elements: ${state.elements.length}, Selection: ${state.selection.selectedElements.length}, Zoom: ${state.canvas.zoom}%`);
        renderCanvas();
    }, [state, canvasSettings, imageLoadCount, selectionState === null || selectionState === void 0 ? void 0 : selectionState.updateTrigger, visibleElementsList]); // Dépendances directes au lieu de renderCanvas
    // Rendu initial - REMOVED: Redondant avec l'effet principal ci-dessus
    // ✅ Force initial render when elements first load (for cached images)
    (0,react.useEffect)(() => {
        if (state.elements.length > 0 && !initialImageCheckDoneRef.current) {
            debugLog(`[Canvas] Initial elements loaded (${state.elements.length} elements) - scheduling image loading checks`);
            initialImageCheckDoneRef.current = true;
            // Force multiple renders to ensure images are displayed
            const timer1 = setTimeout(() => {
                debugLog(`[Canvas] Image loading check #1`);
                setImageLoadCount(prev => prev + 1);
            }, 100);
            const timer2 = setTimeout(() => {
                debugLog(`[Canvas] Image loading check #2`);
                setImageLoadCount(prev => prev + 1);
            }, 500);
            const timer3 = setTimeout(() => {
                debugLog(`[Canvas] Image loading check #3`);
                setImageLoadCount(prev => prev + 1);
            }, 1000);
            // Add longer timeout for slow-loading images
            const timer4 = setTimeout(() => {
                debugLog(`[Canvas] Image loading check #4 (final)`);
                setImageLoadCount(prev => prev + 1);
            }, 2000);
            return () => {
                clearTimeout(timer1);
                clearTimeout(timer2);
                clearTimeout(timer3);
                clearTimeout(timer4);
            };
        }
    }, [state.elements.length]);
    // ✅ CORRECTION 1: Ajouter beforeunload event pour avertir des changements non-sauvegardés
    (0,react.useEffect)(() => {
        const handleBeforeUnload = (e) => {
            if (state.template.isModified) {
                e.preventDefault();
            }
        };
        window.addEventListener('beforeunload', handleBeforeUnload, { passive: true });
        return () => window.removeEventListener('beforeunload', handleBeforeUnload);
    }, [state.template.isModified]);
    // 🎯 Initialize monitoring dashboard
    (0,react.useEffect)(() => {
        CanvasMonitoringDashboard.initialize();
        // Silent initialization
    }, []);
    // Calculate border style based on canvas settings
    const borderStyle = isDragOver
        ? '2px solid #007acc'
        : ((canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.borderWidth) && canvasSettings.borderWidth > 0)
            ? `${canvasSettings.borderWidth}px solid ${(canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.borderColor) || '#cccccc'}`
            : 'none';
    // Calculate canvas display size based on zoom
    const zoomScale = state.canvas.zoom / 100;
    const displayWidth = width * zoomScale;
    const displayHeight = height * zoomScale;
    debugLog(`[Canvas] Rendering canvas element - Display size: ${displayWidth}x${displayHeight}, Border: ${borderStyle}, Drag over: ${isDragOver}`);
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("canvas", { ref: canvasRef, width: width, height: height, className: className, onClick: handleCanvasClick, onMouseDown: handleMouseDown, onMouseMove: handleMouseMove, onMouseUp: handleMouseUp, onContextMenu: handleCanvasContextMenu, onDrop: handleDrop, onDragOver: handleDragOver, onDragLeave: handleDragLeave, style: {
                    width: `${displayWidth}px`,
                    height: `${displayHeight}px`,
                    border: borderStyle,
                    cursor: 'crosshair',
                    backgroundColor: (canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.canvasBackgroundColor) || '#ffffff',
                    boxShadow: (canvasSettings === null || canvasSettings === void 0 ? void 0 : canvasSettings.shadowEnabled) ? '2px 8px 16px rgba(0, 0, 0, 0.3), 0 4px 8px rgba(0, 0, 0, 0.2)' : 'none',
                    transition: 'border-color 0.2s ease, box-shadow 0.2s ease'
                } }), contextMenu.isVisible && ((0,jsx_runtime.jsx)(ContextMenu, { items: getContextMenuItems(contextMenu.elementId), position: contextMenu.position, onClose: hideContextMenu, isVisible: contextMenu.isVisible }))] }));
};

;// ./assets/js/pdf-builder-react/constants/responsive.ts
/**
 * Responsive Design Constants
 * Breakpoints et utilitaires pour le design responsive
 */
const BREAKPOINTS = {
    // Mobile first approach
    xs: 480,
    sm: 768,
    md: 992,
    lg: 1200,
    xl: 1440, // Extra large devices (large desktops, >= 1440px)
};
const MEDIA_QUERIES = {
    xs: `(max-width: ${BREAKPOINTS.xs - 1}px)`,
    sm: `(max-width: ${BREAKPOINTS.sm - 1}px)`,
    md: `(max-width: ${BREAKPOINTS.md - 1}px)`,
    lg: `(max-width: ${BREAKPOINTS.lg - 1}px)`,
    xl: `(min-width: ${BREAKPOINTS.xl}px)`,
    // Ranges
    xsOnly: `(max-width: ${BREAKPOINTS.xs - 1}px)`,
    smOnly: `(min-width: ${BREAKPOINTS.xs}px) and (max-width: ${BREAKPOINTS.sm - 1}px)`,
    mdOnly: `(min-width: ${BREAKPOINTS.sm}px) and (max-width: ${BREAKPOINTS.md - 1}px)`,
    lgOnly: `(min-width: ${BREAKPOINTS.md}px) and (max-width: ${BREAKPOINTS.lg - 1}px)`,
    xlOnly: `(min-width: ${BREAKPOINTS.lg}px)`,
};

;// ./assets/js/pdf-builder-react/hooks/useResponsive.ts


/**
 * Hook personnalisé pour détecter les media queries
 * @param query - La media query à vérifier
 * @returns boolean - True si la media query correspond
 */
function useMediaQuery(query) {
    const [matches, setMatches] = (0,react.useState)(false);
    (0,react.useEffect)(() => {
        const mediaQuery = window.matchMedia(MEDIA_QUERIES[query]);
        // Fonction de callback pour les changements
        const handleChange = (event) => {
            setMatches(event.matches);
        };
        // Vérifier initialement
        setMatches(mediaQuery.matches);
        // Écouter les changements
        mediaQuery.addEventListener('change', handleChange);
        // Cleanup
        return () => {
            mediaQuery.removeEventListener('change', handleChange);
        };
    }, [query]);
    return matches;
}
/**
 * Hook pour obtenir le breakpoint actuel
 * @returns Le breakpoint actuel ('xs', 'sm', 'md', 'lg', 'xl')
 */
function useResponsive_useBreakpoint() {
    const isXs = useMediaQuery('xsOnly');
    const isSm = useMediaQuery('smOnly');
    const isMd = useMediaQuery('mdOnly');
    const isLg = useMediaQuery('lgOnly');
    const isXl = useMediaQuery('xlOnly');
    if (isXs)
        return 'xs';
    if (isSm)
        return 'sm';
    if (isMd)
        return 'md';
    if (isLg)
        return 'lg';
    return 'xl'; // Default to xl for very large screens
}
/**
 * Hook pour vérifier si l'écran est mobile
 * @returns boolean - True si mobile (xs ou sm)
 */
function useResponsive_useIsMobile() {
    const breakpoint = useResponsive_useBreakpoint();
    return breakpoint === 'xs' || breakpoint === 'sm';
}
/**
 * Hook pour vérifier si l'écran est desktop
 * @returns boolean - True si desktop (lg ou xl)
 */
function useResponsive_useIsDesktop() {
    const breakpoint = useResponsive_useBreakpoint();
    return breakpoint === 'lg' || breakpoint === 'xl';
}
/**
 * Hook pour vérifier si l'écran est tablette
 * @returns boolean - True si tablette (md)
 */
function useResponsive_useIsTablet() {
    const breakpoint = useResponsive_useBreakpoint();
    return breakpoint === 'md';
}

;// ./assets/js/pdf-builder-react/components/ui/Responsive.tsx


/**
 * Composant Responsive - Affiche du contenu différent selon le breakpoint
 */
function Responsive({ children, breakpoint, mobile, tablet, desktop, className = '' }) {
    const currentBreakpoint = useBreakpoint();
    const isMobile = useIsMobile();
    const isTablet = useIsTablet();
    const isDesktop = useIsDesktop();
    // Si un breakpoint spécifique est demandé
    if (breakpoint && currentBreakpoint === breakpoint) {
        return _jsx("div", { className: className, children: children });
    }
    // Contenu spécifique selon le type d'appareil
    if (isMobile && mobile) {
        return _jsx("div", { className: className, children: mobile });
    }
    if (isTablet && tablet) {
        return _jsx("div", { className: className, children: tablet });
    }
    if (isDesktop && desktop) {
        return _jsx("div", { className: className, children: desktop });
    }
    // Contenu par défaut
    return _jsx("div", { className: className, children: children });
}
/**
 * Conteneur responsive qui applique des classes CSS différentes selon l'appareil
 */
function ResponsiveContainer({ children, className = '', mobileClass = '', tabletClass = '', desktopClass = '' }) {
    const isMobile = useResponsive_useIsMobile();
    const isTablet = useResponsive_useIsTablet();
    const isDesktop = useResponsive_useIsDesktop();
    let responsiveClass = className;
    if (isMobile && mobileClass) {
        responsiveClass += ` ${mobileClass}`;
    }
    else if (isTablet && tabletClass) {
        responsiveClass += ` ${tabletClass}`;
    }
    else if (isDesktop && desktopClass) {
        responsiveClass += ` ${desktopClass}`;
    }
    return ((0,jsx_runtime.jsx)("div", { className: responsiveClass.trim(), children: children }));
}
/**
 * Composant Hidden - Cache le contenu sur certains breakpoints
 */
function Hidden({ children, on = [], className = '' }) {
    const currentBreakpoint = useBreakpoint();
    if (on.includes(currentBreakpoint)) {
        return null;
    }
    return _jsx("div", { className: className, children: children });
}
/**
 * Composant Visible - Affiche le contenu seulement sur certains breakpoints
 */
function Visible({ children, on = [], className = '' }) {
    const currentBreakpoint = useBreakpoint();
    if (!on.includes(currentBreakpoint)) {
        return null;
    }
    return _jsx("div", { className: className, children: children });
}

;// ./assets/js/pdf-builder-react/components/toolbar/Toolbar.tsx





function Toolbar({ className }) {
    const { state, dispatch, setMode, undo, redo, reset, toggleGrid, toggleGuides, setCanvas } = useBuilder();
    const canvasSettings = CanvasSettingsContext_useCanvasSettings();
    const isMobile = useResponsive_useIsMobile();
    const isTablet = useResponsive_useIsTablet();
    // Vérifications de sécurité
    if (!state) {
        return (0,jsx_runtime.jsx)("div", { style: { padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }, children: "Erreur: \u00C9tat Builder non disponible" });
    }
    if (!state.history) {
        return (0,jsx_runtime.jsx)("div", { style: { padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }, children: "Erreur: Historique non disponible" });
    }
    if (!state.canvas) {
        return (0,jsx_runtime.jsx)("div", { style: { padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }, children: "Erreur: Canvas non disponible" });
    }
    const tools = [
        { mode: 'select', label: 'Sélection', icon: '🖱️' },
        { mode: 'rectangle', label: 'Rectangle', icon: '▭' },
        { mode: 'circle', label: 'Cercle', icon: '○' },
        { mode: 'text', label: 'Texte', icon: 'T' },
        { mode: 'line', label: 'Ligne', icon: '━' },
        { mode: 'image', label: 'Image', icon: '🖼️' },
    ];
    const handleModeChange = (mode) => {
        if (setMode) {
            setMode(mode);
        }
    };
    const handleUndo = () => {
        if (undo) {
            undo();
        }
    };
    const handleRedo = () => {
        if (redo) {
            redo();
        }
    };
    const handleReset = () => {
        if (reset) {
            reset();
        }
    };
    const handleToggleGrid = () => {
        if (toggleGrid && canvasSettings.gridShow) {
            toggleGrid();
        }
    };
    const handleToggleGuides = () => {
        if (toggleGuides && canvasSettings.guidesEnabled) {
            toggleGuides();
        }
    };
    const handleToggleSnapToGrid = () => {
        // Vérifier que la grille globale est activée avant d'autoriser l'accrochage
        if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled) {
            const newSnapToGrid = !state.canvas.snapToGrid;
            if (setCanvas) {
                setCanvas({ snapToGrid: newSnapToGrid });
            }
        }
    };
    return ((0,jsx_runtime.jsx)(ResponsiveContainer, { className: `pdf-builder-toolbar ${className || ''}`, mobileClass: "toolbar-mobile", tabletClass: "toolbar-tablet", desktopClass: "toolbar-desktop", children: (0,jsx_runtime.jsx)("div", { style: {
                display: 'flex',
                flexDirection: isMobile ? 'row' : 'column',
                gap: isMobile ? '8px' : '12px',
                padding: isMobile ? '8px' : '16px',
                backgroundColor: '#ffffff',
                border: '1px solid #e1e5e9',
                borderRadius: '8px',
                boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
                maxHeight: isMobile ? 'auto' : '140px',
                overflowX: isMobile ? 'auto' : 'visible',
                overflowY: isMobile ? 'visible' : 'auto'
            }, children: (0,jsx_runtime.jsxs)("div", { style: {
                    display: 'flex',
                    gap: isMobile ? '8px' : '16px',
                    alignItems: isMobile ? 'center' : 'flex-start',
                    flexDirection: isMobile ? 'column' : 'row',
                    minWidth: isMobile ? 'auto' : '220px'
                }, children: [(0,jsx_runtime.jsxs)("section", { style: {
                            display: 'flex',
                            flexDirection: 'column',
                            gap: '8px',
                            minWidth: isMobile ? 'auto' : '220px',
                            flex: isMobile ? '1' : 'none'
                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: isMobile ? '11px' : '13px',
                                    fontWeight: '600',
                                    color: '#374151',
                                    textTransform: 'uppercase',
                                    letterSpacing: '0.5px',
                                    borderLeft: '3px solid #3b82f6',
                                    paddingLeft: '8px',
                                    display: isMobile ? 'none' : 'block'
                                }, children: "Outils" }), (0,jsx_runtime.jsx)("div", { style: {
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    gap: '6px',
                                    maxHeight: '80px',
                                    alignContent: 'flex-start'
                                }, children: tools.map(tool => ((0,jsx_runtime.jsxs)("button", { onClick: () => handleModeChange(tool.mode), style: {
                                        padding: '8px 12px',
                                        border: '1px solid #d1d5db',
                                        borderRadius: '6px',
                                        backgroundColor: state.mode === tool.mode ? '#3b82f6' : '#ffffff',
                                        color: state.mode === tool.mode ? '#ffffff' : '#374151',
                                        cursor: 'pointer',
                                        fontSize: '13px',
                                        fontWeight: '500',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '6px',
                                        transition: 'all 0.2s ease',
                                        boxShadow: state.mode === tool.mode ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                                        minWidth: '90px',
                                        justifyContent: 'center'
                                    }, onMouseEnter: (e) => {
                                        if (state.mode !== tool.mode) {
                                            e.currentTarget.style.backgroundColor = '#f8fafc';
                                            e.currentTarget.style.borderColor = '#9ca3af';
                                        }
                                    }, onMouseLeave: (e) => {
                                        if (state.mode !== tool.mode) {
                                            e.currentTarget.style.backgroundColor = '#ffffff';
                                            e.currentTarget.style.borderColor = '#d1d5db';
                                        }
                                    }, children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '14px' }, children: tool.icon }), (0,jsx_runtime.jsx)("span", { children: tool.label })] }, tool.mode))) })] }), (0,jsx_runtime.jsxs)("section", { style: { display: 'flex', flexDirection: 'column', gap: '8px', flex: 1 }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '13px',
                                    fontWeight: '600',
                                    color: '#374151',
                                    textTransform: 'uppercase',
                                    letterSpacing: '0.5px',
                                    borderLeft: '3px solid #10b981',
                                    paddingLeft: '8px'
                                }, children: "Actions" }), (0,jsx_runtime.jsxs)("div", { style: {
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    gap: '6px',
                                    maxHeight: '80px',
                                    alignContent: 'flex-start'
                                }, children: [(0,jsx_runtime.jsx)("button", { onClick: handleUndo, disabled: !state.history.canUndo, style: {
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            backgroundColor: state.history.canUndo ? '#ffffff' : '#f9fafb',
                                            color: state.history.canUndo ? '#374151' : '#9ca3af',
                                            cursor: state.history.canUndo ? 'pointer' : 'not-allowed',
                                            fontSize: '13px',
                                            fontWeight: '500',
                                            transition: 'all 0.2s ease',
                                            minWidth: '90px'
                                        }, onMouseEnter: (e) => {
                                            if (state.history.canUndo) {
                                                e.currentTarget.style.backgroundColor = '#f8fafc';
                                                e.currentTarget.style.borderColor = '#9ca3af';
                                            }
                                        }, onMouseLeave: (e) => {
                                            if (state.history.canUndo) {
                                                e.currentTarget.style.backgroundColor = '#ffffff';
                                                e.currentTarget.style.borderColor = '#d1d5db';
                                            }
                                        }, children: "\u21B6 Annuler" }), (0,jsx_runtime.jsx)("button", { onClick: handleRedo, disabled: !state.history.canRedo, style: {
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            backgroundColor: state.history.canRedo ? '#ffffff' : '#f9fafb',
                                            color: state.history.canRedo ? '#374151' : '#9ca3af',
                                            cursor: state.history.canRedo ? 'pointer' : 'not-allowed',
                                            fontSize: '13px',
                                            fontWeight: '500',
                                            transition: 'all 0.2s ease',
                                            minWidth: '90px'
                                        }, onMouseEnter: (e) => {
                                            if (state.history.canRedo) {
                                                e.currentTarget.style.backgroundColor = '#f8fafc';
                                                e.currentTarget.style.borderColor = '#9ca3af';
                                            }
                                        }, onMouseLeave: (e) => {
                                            if (state.history.canRedo) {
                                                e.currentTarget.style.backgroundColor = '#ffffff';
                                                e.currentTarget.style.borderColor = '#d1d5db';
                                            }
                                        }, children: "\u21B7 R\u00E9tablir" }), (0,jsx_runtime.jsx)("button", { onClick: handleToggleGrid, disabled: !canvasSettings.gridShow, style: {
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            backgroundColor: !canvasSettings.gridShow ? '#f9fafb' : (state.canvas.showGrid ? '#3b82f6' : '#ffffff'),
                                            color: !canvasSettings.gridShow ? '#9ca3af' : (state.canvas.showGrid ? '#ffffff' : '#374151'),
                                            cursor: !canvasSettings.gridShow ? 'not-allowed' : 'pointer',
                                            fontSize: '13px',
                                            fontWeight: '500',
                                            transition: 'all 0.2s ease',
                                            boxShadow: state.canvas.showGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                                            opacity: !canvasSettings.gridShow ? 0.6 : 1,
                                            minWidth: '90px'
                                        }, onMouseEnter: (e) => {
                                            if (canvasSettings.gridShow && !state.canvas.showGrid) {
                                                e.currentTarget.style.backgroundColor = '#f8fafc';
                                                e.currentTarget.style.borderColor = '#9ca3af';
                                            }
                                        }, onMouseLeave: (e) => {
                                            if (canvasSettings.gridShow && !state.canvas.showGrid) {
                                                e.currentTarget.style.backgroundColor = '#ffffff';
                                                e.currentTarget.style.borderColor = '#d1d5db';
                                            }
                                        }, children: state.canvas.showGrid ? '⬜ Grille' : '▦ Grille' }), (0,jsx_runtime.jsx)("button", { onClick: handleToggleSnapToGrid, disabled: !canvasSettings.gridShow || !canvasSettings.gridSnapEnabled, style: {
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            backgroundColor: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? '#f9fafb' : (state.canvas.snapToGrid ? '#3b82f6' : '#ffffff'),
                                            color: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? '#9ca3af' : (state.canvas.snapToGrid ? '#ffffff' : '#374151'),
                                            cursor: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? 'not-allowed' : 'pointer',
                                            fontSize: '13px',
                                            fontWeight: '500',
                                            transition: 'all 0.2s ease',
                                            boxShadow: state.canvas.snapToGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                                            opacity: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? 0.6 : 1,
                                            minWidth: '90px'
                                        }, onMouseEnter: (e) => {
                                            if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled && !state.canvas.snapToGrid) {
                                                e.currentTarget.style.backgroundColor = '#f8fafc';
                                                e.currentTarget.style.borderColor = '#9ca3af';
                                            }
                                        }, onMouseLeave: (e) => {
                                            if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled && !state.canvas.snapToGrid) {
                                                e.currentTarget.style.backgroundColor = '#ffffff';
                                                e.currentTarget.style.borderColor = '#d1d5db';
                                            }
                                        }, children: "\uD83E\uDDF2 Snap" }), (0,jsx_runtime.jsx)("button", { onClick: handleToggleGuides, disabled: !canvasSettings.guidesEnabled, style: {
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            backgroundColor: !canvasSettings.guidesEnabled ? '#f9fafb' : (state.template.showGuides ? '#3b82f6' : '#ffffff'),
                                            color: !canvasSettings.guidesEnabled ? '#9ca3af' : (state.template.showGuides ? '#ffffff' : '#374151'),
                                            cursor: !canvasSettings.guidesEnabled ? 'not-allowed' : 'pointer',
                                            fontSize: '13px',
                                            fontWeight: '500',
                                            transition: 'all 0.2s ease',
                                            boxShadow: state.template.showGuides ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                                            opacity: !canvasSettings.guidesEnabled ? 0.6 : 1,
                                            minWidth: '90px'
                                        }, onMouseEnter: (e) => {
                                            if (canvasSettings.guidesEnabled && !state.template.showGuides) {
                                                e.currentTarget.style.backgroundColor = '#f8fafc';
                                                e.currentTarget.style.borderColor = '#9ca3af';
                                            }
                                        }, onMouseLeave: (e) => {
                                            if (canvasSettings.guidesEnabled && !state.template.showGuides) {
                                                e.currentTarget.style.backgroundColor = '#ffffff';
                                                e.currentTarget.style.borderColor = '#d1d5db';
                                            }
                                        }, children: state.template.showGuides ? '📏 Guides' : '📐 Guides' }), (0,jsx_runtime.jsxs)("div", { style: {
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: '4px',
                                            padding: '6px 10px',
                                            backgroundColor: '#f8fafc',
                                            borderRadius: '6px',
                                            border: '1px solid #e2e8f0'
                                        }, children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '12px', color: '#64748b', fontWeight: '500' }, children: "\uD83D\uDD0D" }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                                    // Zoom out
                                                    const newZoom = Math.max(canvasSettings.zoomMin, state.canvas.zoom - canvasSettings.zoomStep);
                                                    if (setCanvas) {
                                                        setCanvas({ zoom: newZoom });
                                                    }
                                                }, style: {
                                                    padding: '2px 6px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '4px',
                                                    backgroundColor: '#ffffff',
                                                    color: '#374151',
                                                    cursor: 'pointer',
                                                    fontSize: '12px',
                                                    fontWeight: '600',
                                                    minWidth: '24px'
                                                }, title: "Zoom arri\u00E8re", children: "\u2796" }), (0,jsx_runtime.jsxs)("span", { style: {
                                                    fontSize: '12px',
                                                    fontWeight: '600',
                                                    color: '#374151',
                                                    minWidth: '40px',
                                                    textAlign: 'center'
                                                }, children: [state.canvas.zoom, "%"] }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                                    // Zoom in
                                                    const newZoom = Math.min(canvasSettings.zoomMax, state.canvas.zoom + canvasSettings.zoomStep);
                                                    if (setCanvas) {
                                                        setCanvas({ zoom: newZoom });
                                                    }
                                                }, style: {
                                                    padding: '2px 6px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '4px',
                                                    backgroundColor: '#ffffff',
                                                    color: '#374151',
                                                    cursor: 'pointer',
                                                    fontSize: '12px',
                                                    fontWeight: '600',
                                                    minWidth: '24px'
                                                }, title: "Zoom avant", children: "\u2795" }), (0,jsx_runtime.jsx)("span", { style: { fontSize: '10px', color: '#94a3b8', margin: '0 2px' }, children: "|" }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                                    // Fit to screen (zoom to fit canvas)
                                                    if (setCanvas) {
                                                        const fitZoom = Math.max(canvasSettings.zoomMin, Math.min(100, canvasSettings.zoomMax));
                                                        setCanvas({ zoom: fitZoom });
                                                    }
                                                }, style: {
                                                    padding: '4px 8px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '4px',
                                                    backgroundColor: '#ffffff',
                                                    color: '#374151',
                                                    cursor: 'pointer',
                                                    fontSize: '11px',
                                                    fontWeight: '500'
                                                }, title: "Adapter \u00E0 l'\u00E9cran", children: "\uD83D\uDD04" })] })] })] }), (0,jsx_runtime.jsxs)("section", { style: { display: 'flex', flexDirection: 'column', gap: '6px', minWidth: '160px', marginLeft: 'auto' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '13px',
                                    fontWeight: '600',
                                    color: '#374151',
                                    textTransform: 'uppercase',
                                    letterSpacing: '0.5px',
                                    borderLeft: '3px solid #f59e0b',
                                    paddingLeft: '8px'
                                }, children: "Infos" }), (0,jsx_runtime.jsxs)("div", { style: {
                                    fontSize: '12px',
                                    color: '#6b7280',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: '2px',
                                    backgroundColor: '#f9fafb',
                                    padding: '6px',
                                    borderRadius: '6px',
                                    border: '1px solid #e5e7eb'
                                }, children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', justifyContent: 'space-between' }, children: [(0,jsx_runtime.jsx)("span", { children: "\u00C9l\u00E9ments:" }), (0,jsx_runtime.jsx)("span", { style: { fontWeight: '600', color: '#374151' }, children: state.elements.length })] }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', justifyContent: 'space-between' }, children: [(0,jsx_runtime.jsx)("span", { children: "S\u00E9lection:" }), (0,jsx_runtime.jsx)("span", { style: { fontWeight: '600', color: '#374151' }, children: state.selection.selectedElements.length })] }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', justifyContent: 'space-between' }, children: [(0,jsx_runtime.jsx)("span", { children: "Mode:" }), (0,jsx_runtime.jsx)("span", { style: { fontWeight: '600', color: '#374151' }, children: state.mode })] }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', justifyContent: 'space-between' }, children: [(0,jsx_runtime.jsx)("span", { children: "Zoom:" }), (0,jsx_runtime.jsxs)("span", { style: { fontWeight: '600', color: '#374151' }, children: [state.canvas.zoom, "%"] })] })] })] })] }) }) }));
}

;// ./assets/js/pdf-builder-react/components/properties/ProductTableProperties.tsx


// Composant Accordion personnalisé
const Accordion = ({ title, children, defaultOpen = false }) => {
    const [isOpen, setIsOpen] = (0,react.useState)(defaultOpen);
    return ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => setIsOpen(!isOpen), style: {
                    padding: '12px',
                    backgroundColor: '#f8f9fa',
                    cursor: 'pointer',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    borderBottom: isOpen ? '1px solid #e9ecef' : 'none'
                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: title }), (0,jsx_runtime.jsx)("span", { style: {
                            fontSize: '12px',
                            color: '#6c757d',
                            transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)'
                        }, children: "\u25BC" })] }), (0,jsx_runtime.jsx)("div", { style: {
                    maxHeight: isOpen ? '1000px' : '0px',
                    overflow: 'hidden',
                    padding: isOpen ? '12px' : '0px 12px',
                    backgroundColor: '#ffffff'
                }, children: children })] }));
};
// Composant Toggle personnalisé
const Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function ProductTableProperties({ element, onChange, activeTab, setActiveTab }) {
    const currentTab = activeTab[element.id] || 'fonctionnalites';
    const setCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: currentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: currentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: currentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: currentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: currentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: currentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), currentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Structure du tableau" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(Toggle, { checked: element.showHeaders !== false, onChange: (checked) => onChange(element.id, 'showHeaders', checked), label: "Afficher les en-t\u00EAtes", description: "Affiche les noms des colonnes" }), (0,jsx_runtime.jsx)(Toggle, { checked: element.showBorders !== false, onChange: (checked) => onChange(element.id, 'showBorders', checked), label: "Afficher les bordures", description: "Affiche les bordures du tableau" }), (0,jsx_runtime.jsx)(Toggle, { checked: element.showAlternatingRows !== false, onChange: (checked) => onChange(element.id, 'showAlternatingRows', checked), label: "Lignes altern\u00E9es", description: "Alterne les couleurs des lignes" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Colonnes produits" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(Toggle, { checked: element.showSku !== false, onChange: (checked) => onChange(element.id, 'showSku', checked), label: "Afficher les SKU", description: "Colonne des r\u00E9f\u00E9rences produit" }), (0,jsx_runtime.jsx)(Toggle, { checked: element.showDescription !== false, onChange: (checked) => onChange(element.id, 'showDescription', checked), label: "Afficher les descriptions", description: "Colonne des descriptions courtes" }), (0,jsx_runtime.jsx)(Toggle, { checked: element.showQuantity !== false, onChange: (checked) => onChange(element.id, 'showQuantity', checked), label: "Afficher la quantit\u00E9", description: "Colonne quantit\u00E9 des produits" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "\u00C9l\u00E9ments de calcul" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(Toggle, { checked: element.showShipping !== false, onChange: (checked) => onChange(element.id, 'showShipping', checked), label: "Afficher les frais de port", description: "Affiche les frais de livraison" }), (0,jsx_runtime.jsx)(Toggle, { checked: element.showTax !== false, onChange: (checked) => onChange(element.id, 'showTax', checked), label: "Afficher la TVA", description: "Affiche les taxes sur le total" }), (0,jsx_runtime.jsx)(Toggle, { checked: element.showGlobalDiscount !== false, onChange: (checked) => onChange(element.id, 'showGlobalDiscount', checked), label: "Afficher la remise globale", description: "Affiche la remise globale appliqu\u00E9e" })] })] })] })), currentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)(Accordion, { title: "Police globale du tableau", defaultOpen: true, children: [(0,jsx_runtime.jsxs)("div", { style: {
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: '12px'
                                }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px', color: '#007bff' }, children: "Taille" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.globalFontSize || 11, onChange: (e) => onChange(element.id, 'globalFontSize', parseInt(e.target.value) || 11), style: {
                                                    width: '100%',
                                                    padding: '4px 6px',
                                                    border: '1px solid #007bff',
                                                    borderRadius: '3px',
                                                    fontSize: '11px',
                                                    backgroundColor: 'white'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px', color: '#007bff' }, children: "Police" }), (0,jsx_runtime.jsxs)("select", { value: element.globalFontFamily || 'Arial', onChange: (e) => onChange(element.id, 'globalFontFamily', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 6px',
                                                    border: '1px solid #007bff',
                                                    borderRadius: '3px',
                                                    fontSize: '11px',
                                                    backgroundColor: 'white'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px', color: '#007bff' }, children: "\u00C9paisseur" }), (0,jsx_runtime.jsxs)("select", { value: element.globalFontWeight || 'normal', onChange: (e) => onChange(element.id, 'globalFontWeight', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 6px',
                                                    border: '1px solid #007bff',
                                                    borderRadius: '3px',
                                                    fontSize: '11px',
                                                    backgroundColor: 'white'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras" })] })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px', color: '#007bff' }, children: "Style" }), (0,jsx_runtime.jsxs)("select", { value: element.globalFontStyle || 'normal', onChange: (e) => onChange(element.id, 'globalFontStyle', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 6px',
                                                    border: '1px solid #007bff',
                                                    borderRadius: '3px',
                                                    fontSize: '11px',
                                                    backgroundColor: 'white'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] })] }), (0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '10px',
                                    color: '#666',
                                    marginTop: '8px',
                                    textAlign: 'center',
                                    fontStyle: 'italic'
                                }, children: "Ces param\u00E8tres s'appliquent \u00E0 tout le tableau. Vous pouvez les personnaliser par zone ci-dessous." })] }), !element.globalFontEnabled && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)(Accordion, { title: "Police de l'ent\u00EAte", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police de l'ent\u00EAte" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.headerFontSize || element.globalFontSize || 12, onChange: (e) => onChange(element.id, 'headerFontSize', parseInt(e.target.value) || 12), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                } }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontSize || 11, "px"] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police de l'ent\u00EAte" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontFamily || element.globalFontFamily || 'Arial', onChange: (e) => onChange(element.id, 'headerFontFamily', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontFamily || 'Arial'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police de l'ent\u00EAte" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontWeight || element.globalFontWeight || 'bold', onChange: (e) => onChange(element.id, 'headerFontWeight', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontWeight || 'normal'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police de l'ent\u00EAte" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontStyle || element.globalFontStyle || 'normal', onChange: (e) => onChange(element.id, 'headerFontStyle', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontStyle || 'normal'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte de l'ent\u00EAte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerTextColor || '#374151', onChange: (e) => onChange(element.id, 'headerTextColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: "D\u00E9faut: #374151" })] })] }), (0,jsx_runtime.jsxs)(Accordion, { title: "Police des lignes", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police des lignes" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.rowFontSize || element.globalFontSize || 11, onChange: (e) => onChange(element.id, 'rowFontSize', parseInt(e.target.value) || 11), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                } }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontSize || 11, "px"] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police des lignes" }), (0,jsx_runtime.jsxs)("select", { value: element.rowFontFamily || element.globalFontFamily || 'Arial', onChange: (e) => onChange(element.id, 'rowFontFamily', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontFamily || 'Arial'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police des lignes" }), (0,jsx_runtime.jsxs)("select", { value: element.rowFontWeight || element.globalFontWeight || 'normal', onChange: (e) => onChange(element.id, 'rowFontWeight', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontWeight || 'normal'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police des lignes" }), (0,jsx_runtime.jsxs)("select", { value: element.rowFontStyle || element.globalFontStyle || 'normal', onChange: (e) => onChange(element.id, 'rowFontStyle', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontStyle || 'normal'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte des lignes" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.rowTextColor || '#111827', onChange: (e) => onChange(element.id, 'rowTextColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: "D\u00E9faut: #111827" })] })] }), (0,jsx_runtime.jsxs)(Accordion, { title: "Police des totaux", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police des totaux" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.totalFontSize || element.globalFontSize || 12, onChange: (e) => onChange(element.id, 'totalFontSize', parseInt(e.target.value) || 12), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                } }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontSize || 12, "px"] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police des totaux" }), (0,jsx_runtime.jsxs)("select", { value: element.totalFontFamily || element.globalFontFamily || 'Arial', onChange: (e) => onChange(element.id, 'totalFontFamily', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontFamily || 'Arial'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police des totaux" }), (0,jsx_runtime.jsxs)("select", { value: element.totalFontWeight || element.globalFontWeight || 'bold', onChange: (e) => onChange(element.id, 'totalFontWeight', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontWeight || 'bold'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police des totaux" }), (0,jsx_runtime.jsxs)("select", { value: element.totalFontStyle || element.globalFontStyle || 'normal', onChange: (e) => onChange(element.id, 'totalFontStyle', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: ["D\u00E9faut: ", element.globalFontStyle || 'normal'] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte des totaux" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.totalTextColor || '#111827', onChange: (e) => onChange(element.id, 'totalTextColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '2px' }, children: "D\u00E9faut: #111827" })] })] })] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }, children: "Th\u00E8mes pr\u00E9d\u00E9finis" }), (0,jsx_runtime.jsx)("div", { style: {
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: '6px',
                                    maxHeight: '150px',
                                    overflowY: 'auto',
                                    padding: '4px',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    backgroundColor: '#fafafa'
                                }, children: [
                                    {
                                        id: 'classic',
                                        name: 'Classique',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #e5e7eb',
                                                borderRadius: '2px',
                                                backgroundColor: '#ffffff',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '1px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '90%',
                                                        height: '2px',
                                                        backgroundColor: '#f9fafb',
                                                        borderRadius: '1px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '75%',
                                                        height: '2px',
                                                        backgroundColor: '#ffffff',
                                                        borderRadius: '1px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#ffffff',
                                            headerBackgroundColor: '#f9fafb',
                                            alternateRowColor: '#f9fafb',
                                            borderColor: '#e5e7eb',
                                            textColor: '#111827',
                                            headerTextColor: '#374151'
                                        }
                                    },
                                    {
                                        id: 'modern',
                                        name: 'Moderne',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #cbd5e1',
                                                borderRadius: '4px',
                                                backgroundColor: '#f8fafc',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#3b82f6',
                                                        borderRadius: '1px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#ffffff',
                                                        borderRadius: '1px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#f8fafc',
                                            headerBackgroundColor: '#3b82f6',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#f1f5f9',
                                            borderColor: '#cbd5e1',
                                            textColor: '#334155'
                                        }
                                    },
                                    {
                                        id: 'elegant',
                                        name: 'Élégant',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #c4b5fd',
                                                borderRadius: '6px',
                                                backgroundColor: '#fefefe',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#8b5cf6',
                                                        borderRadius: '2px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#faf5ff',
                                                        borderRadius: '2px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fefefe',
                                            headerBackgroundColor: '#8b5cf6',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#faf5ff',
                                            borderColor: '#c4b5fd',
                                            textColor: '#581c87'
                                        }
                                    },
                                    {
                                        id: 'minimal',
                                        name: 'Minimal',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #f3f4f6',
                                                borderRadius: '0px',
                                                backgroundColor: '#ffffff',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '1px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '2px',
                                                        backgroundColor: '#f9fafb'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '2px',
                                                        backgroundColor: '#ffffff'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#ffffff',
                                            headerBackgroundColor: '#f9fafb',
                                            alternateRowColor: '#f9fafb',
                                            borderColor: '#f3f4f6',
                                            textColor: '#374151',
                                            headerTextColor: '#111827'
                                        }
                                    },
                                    {
                                        id: 'corporate',
                                        name: 'Corporate',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #374151',
                                                borderRadius: '0px',
                                                backgroundColor: '#ffffff',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#1f2937'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#f9fafb'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#ffffff',
                                            headerBackgroundColor: '#1f2937',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#f9fafb',
                                            borderColor: '#374151',
                                            textColor: '#111827'
                                        }
                                    },
                                    {
                                        id: 'warm',
                                        name: 'Chaud',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #fed7aa',
                                                borderRadius: '4px',
                                                backgroundColor: '#fff7ed',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#ea580c',
                                                        borderRadius: '1px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#ffedd5',
                                                        borderRadius: '1px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fff7ed',
                                            headerBackgroundColor: '#ea580c',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#ffedd5',
                                            borderColor: '#fed7aa',
                                            textColor: '#9a3412'
                                        }
                                    },
                                    {
                                        id: 'nature',
                                        name: 'Nature',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #bbf7d0',
                                                borderRadius: '6px',
                                                backgroundColor: '#f0fdf4',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#16a34a',
                                                        borderRadius: '2px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#dcfce7',
                                                        borderRadius: '2px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#f0fdf4',
                                            headerBackgroundColor: '#16a34a',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#dcfce7',
                                            borderColor: '#bbf7d0',
                                            textColor: '#14532d'
                                        }
                                    },
                                    {
                                        id: 'dark',
                                        name: 'Sombre',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #374151',
                                                borderRadius: '4px',
                                                backgroundColor: '#1f2937',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#111827',
                                                        borderRadius: '1px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#374151',
                                                        borderRadius: '1px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#1f2937',
                                            headerBackgroundColor: '#111827',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#374151',
                                            borderColor: '#4b5563',
                                            textColor: '#f9fafb'
                                        }
                                    },
                                    {
                                        id: 'ocean',
                                        name: 'Océan',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #0ea5e9',
                                                borderRadius: '8px',
                                                backgroundColor: '#f0f9ff',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#0284c7',
                                                        borderRadius: '3px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#bae6fd',
                                                        borderRadius: '3px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#f0f9ff',
                                            headerBackgroundColor: '#0284c7',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#bae6fd',
                                            borderColor: '#0ea5e9',
                                            textColor: '#0c4a6e'
                                        }
                                    },
                                    {
                                        id: 'sunset',
                                        name: 'Coucher',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #f97316',
                                                borderRadius: '12px',
                                                backgroundColor: '#fff7ed',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#ea580c',
                                                        borderRadius: '4px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#fed7aa',
                                                        borderRadius: '4px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fff7ed',
                                            headerBackgroundColor: '#ea580c',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#fed7aa',
                                            borderColor: '#f97316',
                                            textColor: '#9a3412'
                                        }
                                    },
                                    {
                                        id: 'forest',
                                        name: 'Forêt',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #22c55e',
                                                borderRadius: '6px',
                                                backgroundColor: '#f0fdf4',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#16a34a',
                                                        borderRadius: '2px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#bbf7d0',
                                                        borderRadius: '2px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#f0fdf4',
                                            headerBackgroundColor: '#16a34a',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#bbf7d0',
                                            borderColor: '#22c55e',
                                            textColor: '#14532d'
                                        }
                                    },
                                    {
                                        id: 'royal',
                                        name: 'Royal',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #7c2d12',
                                                borderRadius: '4px',
                                                backgroundColor: '#fef2f2',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#991b1b',
                                                        borderRadius: '1px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#fecaca',
                                                        borderRadius: '1px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fef2f2',
                                            headerBackgroundColor: '#991b1b',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#fecaca',
                                            borderColor: '#7c2d12',
                                            textColor: '#450a0a'
                                        }
                                    },
                                    {
                                        id: 'clean',
                                        name: 'Propre',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #d1d5db',
                                                borderRadius: '2px',
                                                backgroundColor: '#ffffff',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '1px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '2px',
                                                        backgroundColor: '#f3f4f6'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '2px',
                                                        backgroundColor: '#ffffff'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#ffffff',
                                            headerBackgroundColor: '#f3f4f6',
                                            alternateRowColor: '#f9fafb',
                                            borderColor: '#d1d5db',
                                            textColor: '#374151',
                                            headerTextColor: '#111827'
                                        }
                                    },
                                    {
                                        id: 'tech',
                                        name: 'Tech',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #6366f1',
                                                borderRadius: '0px',
                                                backgroundColor: '#f8fafc',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#4f46e5'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#e0e7ff'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#f8fafc',
                                            headerBackgroundColor: '#4f46e5',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#e0e7ff',
                                            borderColor: '#6366f1',
                                            textColor: '#312e81'
                                        }
                                    },
                                    {
                                        id: 'vintage',
                                        name: 'Vintage',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '2px solid #92400e',
                                                borderRadius: '0px',
                                                backgroundColor: '#fef3c7',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#b45309',
                                                        borderRadius: '0px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#fde68a',
                                                        borderRadius: '0px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fef3c7',
                                            headerBackgroundColor: '#b45309',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#fde68a',
                                            borderColor: '#92400e',
                                            textColor: '#78350f'
                                        }
                                    },
                                    {
                                        id: 'berry',
                                        name: 'Baies',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #be185d',
                                                borderRadius: '10px',
                                                backgroundColor: '#fdf2f8',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#db2777',
                                                        borderRadius: '5px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#fce7f3',
                                                        borderRadius: '5px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fdf2f8',
                                            headerBackgroundColor: '#db2777',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#fce7f3',
                                            borderColor: '#be185d',
                                            textColor: '#831843'
                                        }
                                    },
                                    {
                                        id: 'mint',
                                        name: 'Menthe',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #059669',
                                                borderRadius: '8px',
                                                backgroundColor: '#ecfdf5',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#047857',
                                                        borderRadius: '4px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#a7f3d0',
                                                        borderRadius: '4px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#ecfdf5',
                                            headerBackgroundColor: '#047857',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#a7f3d0',
                                            borderColor: '#059669',
                                            textColor: '#064e3b'
                                        }
                                    },
                                    {
                                        id: 'lavender',
                                        name: 'Lavande',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #7c3aed',
                                                borderRadius: '12px',
                                                backgroundColor: '#faf5ff',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#6d28d9',
                                                        borderRadius: '6px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#e9d5ff',
                                                        borderRadius: '6px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#faf5ff',
                                            headerBackgroundColor: '#6d28d9',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#e9d5ff',
                                            borderColor: '#7c3aed',
                                            textColor: '#581c87'
                                        }
                                    },
                                    {
                                        id: 'stone',
                                        name: 'Pierre',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #6b7280',
                                                borderRadius: '0px',
                                                backgroundColor: '#f9fafb',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#4b5563'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#e5e7eb'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#f9fafb',
                                            headerBackgroundColor: '#4b5563',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#e5e7eb',
                                            borderColor: '#6b7280',
                                            textColor: '#111827'
                                        }
                                    },
                                    {
                                        id: 'sunshine',
                                        name: 'Soleil',
                                        preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                                width: '100%',
                                                height: '20px',
                                                border: '1px solid #f59e0b',
                                                borderRadius: '16px',
                                                backgroundColor: '#fffbeb',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                gap: '2px'
                                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                        width: '85%',
                                                        height: '3px',
                                                        backgroundColor: '#d97706',
                                                        borderRadius: '8px'
                                                    } }), (0,jsx_runtime.jsx)("div", { style: {
                                                        width: '70%',
                                                        height: '3px',
                                                        backgroundColor: '#fef3c7',
                                                        borderRadius: '8px'
                                                    } })] })),
                                        styles: {
                                            backgroundColor: '#fffbeb',
                                            headerBackgroundColor: '#d97706',
                                            headerTextColor: '#ffffff',
                                            alternateRowColor: '#fef3c7',
                                            borderColor: '#f59e0b',
                                            textColor: '#92400e'
                                        }
                                    }
                                ].map(theme => ((0,jsx_runtime.jsxs)("button", { onClick: () => {
                                        // Appliquer toutes les propriétés du thème
                                        Object.entries(theme.styles).forEach(([property, value]) => {
                                            onChange(element.id, property, value);
                                        });
                                    }, style: {
                                        padding: '6px',
                                        border: '2px solid transparent',
                                        borderRadius: '6px',
                                        backgroundColor: '#ffffff',
                                        cursor: 'pointer',
                                        textAlign: 'center',
                                        minHeight: '50px',
                                        display: 'flex',
                                        flexDirection: 'column',
                                        alignItems: 'center',
                                        gap: '4px'
                                    }, onMouseEnter: (e) => {
                                        e.currentTarget.style.borderColor = '#007bff';
                                    }, onMouseLeave: (e) => {
                                        e.currentTarget.style.borderColor = 'transparent';
                                    }, title: `Appliquer le thème ${theme.name}`, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                fontSize: '10px',
                                                fontWeight: 'bold',
                                                color: '#333',
                                                textAlign: 'center',
                                                lineHeight: '1.2'
                                            }, children: theme.name }), theme.preview] }, theme.id))) })] }), (0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), (0,jsx_runtime.jsxs)(Accordion, { title: "Couleurs", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor || '#ffffff', onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Fond des en-t\u00EAtes" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerBackgroundColor || '#f9fafb', onChange: (e) => onChange(element.id, 'headerBackgroundColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur lignes altern\u00E9es" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.alternateRowColor || '#f9fafb', onChange: (e) => onChange(element.id, 'alternateRowColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur des bordures" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.borderColor || '#e5e7eb', onChange: (e) => onChange(element.id, 'borderColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#111827', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] })] })), currentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Position X ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.x) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", step: "0.1", value: element.x, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, placeholder: "Entrer la valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Valeur en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Position Y ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.y) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", step: "0.1", value: element.y, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, placeholder: "Entrer la valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Valeur en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Largeur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.width) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", step: "0.1", value: element.width, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, placeholder: "Entrer la valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Valeur en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Hauteur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.height) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", step: "0.1", value: element.height, onChange: (e) => onChange(element.id, 'height', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, placeholder: "Entrer la valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Valeur en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement horizontal" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement vertical" }), (0,jsx_runtime.jsxs)("select", { value: element.verticalAlign || 'top', onChange: (e) => onChange(element.id, 'verticalAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "top", children: "Haut" }), (0,jsx_runtime.jsx)("option", { value: "middle", children: "Milieu" }), (0,jsx_runtime.jsx)("option", { value: "bottom", children: "Bas" })] })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/CustomerInfoProperties.tsx


// Composant Accordion personnalisé
const CustomerInfoProperties_Accordion = ({ title, children, defaultOpen = false }) => {
    const [isOpen, setIsOpen] = (0,react.useState)(defaultOpen);
    return ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => setIsOpen(!isOpen), style: {
                    padding: '12px',
                    backgroundColor: '#f8f9fa',
                    cursor: 'pointer',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    borderBottom: isOpen ? '1px solid #e9ecef' : 'none'
                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: title }), (0,jsx_runtime.jsx)("span", { style: {
                            fontSize: '12px',
                            color: '#6c757d',
                            transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)',
                            transition: 'transform 0.2s ease'
                        }, children: "\u25BC" })] }), isOpen && ((0,jsx_runtime.jsx)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: children }))] }));
};
// Composant Toggle personnalisé
const CustomerInfoProperties_Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        transition: 'background-color 0.2s ease',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            transition: 'left 0.2s ease',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function CustomerInfoProperties({ element, onChange, activeTab, setActiveTab }) {
    const customerCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setCustomerCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setCustomerCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: customerCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: customerCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCustomerCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: customerCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: customerCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCustomerCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: customerCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: customerCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), customerCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Structure des informations" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showHeaders !== false, onChange: (checked) => onChange(element.id, 'showHeaders', checked), label: "Afficher les en-t\u00EAtes", description: "Affiche les titres des sections" }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showBackground !== false, onChange: (checked) => onChange(element.id, 'showBackground', checked), label: "Afficher le fond", description: "Affiche un fond color\u00E9 derri\u00E8re les informations" }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showBorders !== false, onChange: (checked) => onChange(element.id, 'showBorders', checked), label: "Afficher les bordures", description: "Affiche les bordures autour des sections" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Informations personnelles" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showName !== false, onChange: (checked) => onChange(element.id, 'showName', checked), label: "Afficher le nom", description: "Nom du client" }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showFullName !== false, onChange: (checked) => onChange(element.id, 'showFullName', checked), label: "Afficher le nom complet", description: "Pr\u00E9nom et nom du client" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Coordonn\u00E9es" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showAddress !== false, onChange: (checked) => onChange(element.id, 'showAddress', checked), label: "Afficher l'adresse", description: "Adresse compl\u00E8te du client" }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showEmail !== false, onChange: (checked) => onChange(element.id, 'showEmail', checked), label: "Afficher l'email", description: "Adresse email du client" }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showPhone !== false, onChange: (checked) => onChange(element.id, 'showPhone', checked), label: "Afficher le t\u00E9l\u00E9phone", description: "Num\u00E9ro de t\u00E9l\u00E9phone du client" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Informations de paiement" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showPaymentMethod !== false, onChange: (checked) => onChange(element.id, 'showPaymentMethod', checked), label: "Afficher le moyen de paiement", description: "M\u00E9thode de paiement utilis\u00E9e" }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Toggle, { checked: element.showTransactionId !== false, onChange: (checked) => onChange(element.id, 'showTransactionId', checked), label: "Afficher l'ID de transaction", description: "Identifiant unique de la transaction" })] })] })] })), customerCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)(CustomerInfoProperties_Accordion, { title: "Disposition", defaultOpen: true, children: (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Type de disposition" }), (0,jsx_runtime.jsxs)("select", { value: element.layout || 'vertical', onChange: (e) => onChange(element.id, 'layout', e.target.value), style: {
                                        width: '100%',
                                        padding: '4px 8px',
                                        border: '1px solid #ccc',
                                        borderRadius: '3px',
                                        fontSize: '12px'
                                    }, children: [(0,jsx_runtime.jsx)("option", { value: "vertical", children: "Verticale" }), (0,jsx_runtime.jsx)("option", { value: "horizontal", children: "Horizontale" }), (0,jsx_runtime.jsx)("option", { value: "compact", children: "Compacte" })] })] }) }), (0,jsx_runtime.jsx)(CustomerInfoProperties_Accordion, { title: "Th\u00E8mes pr\u00E9d\u00E9finis", defaultOpen: false, children: (0,jsx_runtime.jsx)("div", { style: {
                                display: 'grid',
                                gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
                                gap: '8px',
                                maxHeight: '200px',
                                overflowY: 'auto',
                                padding: '4px',
                                border: '1px solid #e0e0e0',
                                borderRadius: '4px',
                                backgroundColor: '#fafafa'
                            }, children: [
                                {
                                    id: 'clean',
                                    name: 'Propre',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #f3f4f6',
                                            borderRadius: '4px',
                                            backgroundColor: '#ffffff',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '1px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '2px',
                                                    backgroundColor: '#f9fafb'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '2px',
                                                    backgroundColor: '#ffffff'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '2px',
                                                    backgroundColor: '#f9fafb'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#ffffff',
                                        borderColor: '#f3f4f6',
                                        textColor: '#374151',
                                        headerTextColor: '#111827'
                                    }
                                },
                                {
                                    id: 'subtle',
                                    name: 'Discret',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #f1f5f9',
                                            borderRadius: '6px',
                                            backgroundColor: '#fafbfc',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#e2e8f0',
                                                    borderRadius: '1px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#fafbfc',
                                                    borderRadius: '1px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#e2e8f0',
                                                    borderRadius: '1px'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#fafbfc',
                                        borderColor: '#f1f5f9',
                                        textColor: '#475569',
                                        headerTextColor: '#334155'
                                    }
                                },
                                {
                                    id: 'elegant',
                                    name: 'Élégant',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '2px solid #f3e8ff',
                                            borderRadius: '8px',
                                            backgroundColor: '#fefefe',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#f3e8ff',
                                                    borderRadius: '2px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#fefefe',
                                                    borderRadius: '2px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#f3e8ff',
                                                    borderRadius: '2px'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#fefefe',
                                        borderColor: '#f3e8ff',
                                        textColor: '#6b21a8',
                                        headerTextColor: '#581c87'
                                    }
                                },
                                {
                                    id: 'corporate',
                                    name: 'Corporate',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #e5e7eb',
                                            borderRadius: '0px',
                                            backgroundColor: '#ffffff',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#f3f4f6'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#ffffff'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#f3f4f6'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#ffffff',
                                        borderColor: '#e5e7eb',
                                        textColor: '#374151',
                                        headerTextColor: '#111827'
                                    }
                                },
                                {
                                    id: 'warm',
                                    name: 'Chaleureux',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #fed7aa',
                                            borderRadius: '6px',
                                            backgroundColor: '#fff8f0',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#fed7aa',
                                                    borderRadius: '1px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#fff8f0',
                                                    borderRadius: '1px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#fed7aa',
                                                    borderRadius: '1px'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#fff8f0',
                                        borderColor: '#fed7aa',
                                        textColor: '#9a3412',
                                        headerTextColor: '#78350f'
                                    }
                                },
                                {
                                    id: 'minimal',
                                    name: 'Minimal',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: 'none',
                                            borderRadius: '0px',
                                            backgroundColor: 'transparent',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '1px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '1px',
                                                    backgroundColor: '#e5e7eb'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '1px',
                                                    backgroundColor: 'transparent'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '1px',
                                                    backgroundColor: '#e5e7eb'
                                                } })] })),
                                    styles: {
                                        backgroundColor: 'transparent',
                                        borderColor: 'transparent',
                                        textColor: '#6b7280',
                                        headerTextColor: '#374151'
                                    }
                                }
                            ].map(theme => ((0,jsx_runtime.jsxs)("button", { onClick: () => {
                                    // Appliquer toutes les propriétés du thème
                                    Object.entries(theme.styles).forEach(([property, value]) => {
                                        onChange(element.id, property, value);
                                    });
                                }, style: {
                                    padding: '6px',
                                    border: '2px solid transparent',
                                    borderRadius: '6px',
                                    backgroundColor: '#ffffff',
                                    cursor: 'pointer',
                                    textAlign: 'center',
                                    transition: 'all 0.2s ease',
                                    minHeight: '70px',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    alignItems: 'center',
                                    gap: '4px'
                                }, onMouseEnter: (e) => {
                                    e.currentTarget.style.borderColor = '#007bff';
                                    e.currentTarget.style.backgroundColor = '#f8f9fa';
                                    e.currentTarget.style.transform = 'translateY(-1px)';
                                }, onMouseLeave: (e) => {
                                    e.currentTarget.style.borderColor = 'transparent';
                                    e.currentTarget.style.backgroundColor = '#ffffff';
                                    e.currentTarget.style.transform = 'translateY(0)';
                                }, title: `Appliquer le thème ${theme.name}`, children: [(0,jsx_runtime.jsx)("div", { style: {
                                            fontSize: '10px',
                                            fontWeight: 'bold',
                                            color: '#333',
                                            textAlign: 'center',
                                            lineHeight: '1.2'
                                        }, children: theme.name }), theme.preview] }, theme.id))) }) }), (0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), element.showHeaders !== false && ((0,jsx_runtime.jsxs)(CustomerInfoProperties_Accordion, { title: "Police de l'en-t\u00EAte", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "32", value: element.headerFontSize || (element.fontSize || 12) + 2, onChange: (e) => onChange(element.id, 'headerFontSize', parseInt(e.target.value) || 14), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontFamily || element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'headerFontFamily', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontWeight || element.fontWeight || 'normal', onChange: (e) => onChange(element.id, 'headerFontWeight', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontStyle || element.fontStyle || 'normal', onChange: (e) => onChange(element.id, 'headerFontStyle', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de police" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerTextColor || element.textColor || '#111827', onChange: (e) => onChange(element.id, 'headerTextColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] })), (0,jsx_runtime.jsxs)(CustomerInfoProperties_Accordion, { title: "Police du corps du texte", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.bodyFontSize || element.fontSize || 12, onChange: (e) => onChange(element.id, 'bodyFontSize', parseInt(e.target.value) || 12), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontFamily || element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'bodyFontFamily', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontWeight || element.fontWeight || 'normal', onChange: (e) => onChange(element.id, 'bodyFontWeight', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontStyle || element.fontStyle || 'normal', onChange: (e) => onChange(element.id, 'bodyFontStyle', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de police" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#374151', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] }), (0,jsx_runtime.jsxs)(CustomerInfoProperties_Accordion, { title: "Couleurs", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte d'en-t\u00EAte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerTextColor || element.textColor || '#111827', onChange: (e) => onChange(element.id, 'headerTextColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte du corps" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#374151', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), element.showBackground !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor === 'transparent' ? '#ffffff' : (element.backgroundColor || '#ffffff'), onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur des bordures" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.borderColor || '#e5e7eb', onChange: (e) => onChange(element.id, 'borderColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] })] })), customerCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x, onChange: (e) => onChange(element.id, 'x', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y, onChange: (e) => onChange(element.id, 'y', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width, onChange: (e) => onChange(element.id, 'width', parseInt(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height, onChange: (e) => onChange(element.id, 'height', parseInt(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement horizontal" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement vertical" }), (0,jsx_runtime.jsxs)("select", { value: element.verticalAlign || 'top', onChange: (e) => onChange(element.id, 'verticalAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "top", children: "Haut" }), (0,jsx_runtime.jsx)("option", { value: "middle", children: "Milieu" }), (0,jsx_runtime.jsx)("option", { value: "bottom", children: "Bas" })] })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/CompanyInfoProperties.tsx


// Composant Toggle personnalisé
const CompanyInfoProperties_Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        transition: 'background-color 0.2s ease',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            transition: 'left 0.2s ease',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function CompanyInfoProperties({ element, onChange, activeTab, setActiveTab }) {
    const companyCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setCompanyCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    // État pour les accordéons de police
    const [fontAccordions, setFontAccordions] = (0,react.useState)({
        headerFont: false,
        bodyFont: false,
        layout: true,
        themes: false,
        colors: false // Accordéon couleurs fermé par défaut
    });
    const toggleAccordion = (accordion) => {
        setFontAccordions(prev => ({
            ...prev,
            [accordion]: !prev[accordion]
        }));
    };
    const companyThemes = [
        {
            id: 'corporate',
            name: 'Corporate',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #1f2937',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '3px',
                            backgroundColor: '#1f2937'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#6b7280'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#9ca3af'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#1f2937',
                textColor: '#374151',
                headerTextColor: '#111827'
            }
        },
        {
            id: 'modern',
            name: 'Moderne',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #3b82f6',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '3px',
                            backgroundColor: '#3b82f6'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#60a5fa'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#93c5fd'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#3b82f6',
                textColor: '#1e40af',
                headerTextColor: '#1e3a8a'
            }
        },
        {
            id: 'elegant',
            name: 'Élégant',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #8b5cf6',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '3px',
                            backgroundColor: '#8b5cf6'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#a78bfa'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#c4b5fd'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#8b5cf6',
                textColor: '#6d28d9',
                headerTextColor: '#581c87'
            }
        },
        {
            id: 'minimal',
            name: 'Minimal',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #e5e7eb',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '2px',
                            backgroundColor: '#374151'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#6b7280'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#9ca3af'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#e5e7eb',
                textColor: '#374151',
                headerTextColor: '#111827'
            }
        },
        {
            id: 'professional',
            name: 'Professionnel',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #059669',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '3px',
                            backgroundColor: '#059669'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#10b981'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#34d399'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#059669',
                textColor: '#065f46',
                headerTextColor: '#064e3b'
            }
        },
        {
            id: 'classic',
            name: 'Classique',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #92400e',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '3px',
                            backgroundColor: '#92400e'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#d97706'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#f59e0b'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#92400e',
                textColor: '#78350f',
                headerTextColor: '#451a03'
            }
        }
    ];
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setCompanyCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: companyCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: companyCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCompanyCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: companyCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: companyCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCompanyCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: companyCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: companyCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), companyCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Structure des informations" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showBackground !== false, onChange: (checked) => onChange(element.id, 'showBackground', checked), label: "Afficher le fond", description: "Affiche un fond color\u00E9 derri\u00E8re les informations" }), (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showBorders !== false, onChange: (checked) => onChange(element.id, 'showBorders', checked), label: "Afficher les bordures", description: "Affiche les bordures autour des sections" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Informations g\u00E9n\u00E9rales" }), (0,jsx_runtime.jsx)("div", { style: { paddingLeft: '8px' }, children: (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showCompanyName !== false, onChange: (checked) => onChange(element.id, 'showCompanyName', checked), label: "Afficher le nom de l'entreprise", description: "Nom de l'entreprise" }) })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Coordonn\u00E9es" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showAddress !== false, onChange: (checked) => onChange(element.id, 'showAddress', checked), label: "Afficher l'adresse", description: "Adresse compl\u00E8te de l'entreprise" }), (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showPhone !== false, onChange: (checked) => onChange(element.id, 'showPhone', checked), label: "Afficher le t\u00E9l\u00E9phone", description: "Num\u00E9ro de t\u00E9l\u00E9phone" }), (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showEmail !== false, onChange: (checked) => onChange(element.id, 'showEmail', checked), label: "Afficher l'email", description: "Adresse email de l'entreprise" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Informations l\u00E9gales" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showSiret !== false, onChange: (checked) => onChange(element.id, 'showSiret', checked), label: "Afficher le num\u00E9ro SIRET", description: "Num\u00E9ro SIRET de l'entreprise" }), (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showVat !== false, onChange: (checked) => onChange(element.id, 'showVat', checked), label: "Afficher le num\u00E9ro TVA", description: "Num\u00E9ro TVA de l'entreprise" }), (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showRcs !== false, onChange: (checked) => onChange(element.id, 'showRcs', checked), label: "Afficher le RCS", description: "Registre du Commerce et des Soci\u00E9t\u00E9s" }), (0,jsx_runtime.jsx)(CompanyInfoProperties_Toggle, { checked: element.showCapital !== false, onChange: (checked) => onChange(element.id, 'showCapital', checked), label: "Afficher le capital social", description: "Montant du capital social de l'entreprise" })] })] })] })), companyCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => setFontAccordions(prev => ({ ...prev, layout: !prev.layout })), style: {
                                    padding: '12px',
                                    backgroundColor: '#f8f9fa',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center',
                                    borderBottom: fontAccordions.layout ? '1px solid #e9ecef' : 'none'
                                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: "Disposition" }), (0,jsx_runtime.jsx)("span", { style: {
                                            fontSize: '12px',
                                            color: '#6c757d',
                                            transform: fontAccordions.layout ? 'rotate(180deg)' : 'rotate(0deg)',
                                            transition: 'transform 0.2s ease'
                                        }, children: "\u25BC" })] }), fontAccordions.layout && ((0,jsx_runtime.jsxs)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Type de disposition" }), (0,jsx_runtime.jsxs)("select", { value: element.layout || 'vertical', onChange: (e) => onChange(element.id, 'layout', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "vertical", children: "Verticale" }), (0,jsx_runtime.jsx)("option", { value: "horizontal", children: "Horizontale" }), (0,jsx_runtime.jsx)("option", { value: "compact", children: "Compacte" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement du texte" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] })] }))] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => setFontAccordions(prev => ({ ...prev, themes: !prev.themes })), style: {
                                    padding: '12px',
                                    backgroundColor: '#f8f9fa',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center',
                                    borderBottom: fontAccordions.themes ? '1px solid #e9ecef' : 'none'
                                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: "Th\u00E8mes pr\u00E9d\u00E9finis" }), (0,jsx_runtime.jsx)("span", { style: {
                                            fontSize: '12px',
                                            color: '#6c757d',
                                            transform: fontAccordions.themes ? 'rotate(180deg)' : 'rotate(0deg)',
                                            transition: 'transform 0.2s ease'
                                        }, children: "\u25BC" })] }), fontAccordions.themes && ((0,jsx_runtime.jsx)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: (0,jsx_runtime.jsx)("div", { style: {
                                        display: 'grid',
                                        gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
                                        gap: '8px',
                                        maxHeight: '200px',
                                        overflowY: 'auto',
                                        padding: '4px',
                                        border: '1px solid #e0e0e0',
                                        borderRadius: '4px',
                                        backgroundColor: '#fafafa'
                                    }, children: companyThemes.map((theme) => ((0,jsx_runtime.jsxs)("div", { onClick: () => onChange(element.id, 'theme', theme.id), style: {
                                            cursor: 'pointer',
                                            border: element.theme === theme.id ? '2px solid #007bff' : '2px solid transparent',
                                            borderRadius: '6px',
                                            padding: '6px',
                                            backgroundColor: '#ffffff',
                                            transition: 'all 0.2s ease'
                                        }, title: theme.name, children: [(0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', fontWeight: 'bold', marginBottom: '4px', textAlign: 'center' }, children: theme.name }), theme.preview] }, theme.id))) }) }))] }), (0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), element.showCompanyName !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => toggleAccordion('headerFont'), style: {
                                    padding: '12px',
                                    backgroundColor: '#f8f9fa',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center',
                                    borderBottom: fontAccordions.headerFont ? '1px solid #e9ecef' : 'none'
                                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: "Police du nom de l'entreprise" }), (0,jsx_runtime.jsx)("span", { style: {
                                            fontSize: '12px',
                                            color: '#6c757d',
                                            transform: fontAccordions.headerFont ? 'rotate(180deg)' : 'rotate(0deg)',
                                            transition: 'transform 0.2s ease'
                                        }, children: "\u25BC" })] }), fontAccordions.headerFont && ((0,jsx_runtime.jsxs)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "10", max: "32", value: element.headerFontSize || Math.round((element.fontSize || 12) * 1.2), onChange: (e) => onChange(element.id, 'headerFontSize', parseInt(e.target.value) || 14), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontFamily || element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'headerFontFamily', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontWeight || 'bold', onChange: (e) => onChange(element.id, 'headerFontWeight', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontStyle || element.fontStyle || 'normal', onChange: (e) => onChange(element.id, 'headerFontStyle', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] })] }))] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => toggleAccordion('bodyFont'), style: {
                                    padding: '12px',
                                    backgroundColor: '#f8f9fa',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center',
                                    borderBottom: fontAccordions.bodyFont ? '1px solid #e9ecef' : 'none'
                                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: "Police des informations" }), (0,jsx_runtime.jsx)("span", { style: {
                                            fontSize: '12px',
                                            color: '#6c757d',
                                            transform: fontAccordions.bodyFont ? 'rotate(180deg)' : 'rotate(0deg)',
                                            transition: 'transform 0.2s ease'
                                        }, children: "\u25BC" })] }), fontAccordions.bodyFont && ((0,jsx_runtime.jsxs)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.bodyFontSize || element.fontSize || 12, onChange: (e) => onChange(element.id, 'bodyFontSize', parseInt(e.target.value) || 12), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontFamily || element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'bodyFontFamily', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontWeight || element.fontWeight || 'normal', onChange: (e) => onChange(element.id, 'bodyFontWeight', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontStyle || element.fontStyle || 'normal', onChange: (e) => onChange(element.id, 'bodyFontStyle', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] })] }))] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => setFontAccordions(prev => ({ ...prev, colors: !prev.colors })), style: {
                                    padding: '12px',
                                    backgroundColor: '#f8f9fa',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center',
                                    borderBottom: fontAccordions.colors ? '1px solid #e9ecef' : 'none'
                                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: "Couleurs" }), (0,jsx_runtime.jsx)("span", { style: {
                                            fontSize: '12px',
                                            color: '#6c757d',
                                            transform: fontAccordions.colors ? 'rotate(180deg)' : 'rotate(0deg)',
                                            transition: 'transform 0.2s ease'
                                        }, children: "\u25BC" })] }), fontAccordions.colors && ((0,jsx_runtime.jsxs)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: [element.showBackground !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor === 'transparent' ? '#ffffff' : (element.backgroundColor || '#ffffff'), onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    cursor: 'pointer'
                                                } })] })), element.showBorders !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur des bordures" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.borderColor === 'transparent' ? '#e5e7eb' : (element.borderColor || '#e5e7eb'), onChange: (e) => onChange(element.id, 'borderColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    cursor: 'pointer'
                                                } })] })), element.showBorders !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de la bordure" }), (0,jsx_runtime.jsxs)("select", { value: String(element.borderWidth || '1'), onChange: (e) => onChange(element.id, 'borderWidth', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '4px 8px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "0.5", children: "Fin (0.5px)" }), (0,jsx_runtime.jsx)("option", { value: "1", children: "Normal (1px)" }), (0,jsx_runtime.jsx)("option", { value: "1.5", children: "Moyen (1.5px)" }), (0,jsx_runtime.jsx)("option", { value: "2", children: "\u00C9pais (2px)" }), (0,jsx_runtime.jsx)("option", { value: "3", children: "Tr\u00E8s \u00E9pais (3px)" })] })] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte d'en-t\u00EAte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerTextColor || element.textColor || '#111827', onChange: (e) => onChange(element.id, 'headerTextColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    cursor: 'pointer'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte du corps" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#374151', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    cursor: 'pointer'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#000000', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                                    width: '100%',
                                                    height: '32px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '3px',
                                                    cursor: 'pointer'
                                                } })] })] }))] })] })), companyCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width || 200, onChange: (e) => onChange(element.id, 'width', parseInt(e.target.value) || 200), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height || 100, onChange: (e) => onChange(element.id, 'height', parseInt(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/CompanyLogoProperties.tsx

function CompanyLogoProperties({ element, onChange, activeTab, setActiveTab }) {
    const logoCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setLogoCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setLogoCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: logoCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: logoCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setLogoCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: logoCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: logoCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setLogoCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: logoCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: logoCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), logoCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "URL du logo" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '6px', alignItems: 'center' }, children: [(0,jsx_runtime.jsx)("input", { type: "text", value: element.src || '', onChange: (e) => onChange(element.id, 'src', e.target.value), placeholder: "https://exemple.com/logo.png", style: {
                                            flex: 1,
                                            padding: '6px',
                                            border: '1px solid #ccc',
                                            borderRadius: '4px',
                                            fontSize: '12px'
                                        } }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                            var _a;
                                            // Ouvrir la bibliothèque de médias WordPress
                                            if (!((_a = window.wp) === null || _a === void 0 ? void 0 : _a.media)) {
                                                const errorMsg = 'Bibliothèque de médias WordPress non disponible (wp_enqueue_media non appelé ?)';
                                                alert(errorMsg + '\n\nSaisissez l\'URL manuellement.');
                                                return;
                                            }
                                            try {
                                                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                                                const mediaUploader = window.wp.media({
                                                    title: 'Sélectionner un logo',
                                                    button: {
                                                        text: 'Utiliser ce logo'
                                                    },
                                                    multiple: false,
                                                    library: {
                                                        type: 'image'
                                                    }
                                                });
                                                // Écouter l'événement select avec closure pour avoir accès à mediaUploader
                                                mediaUploader.on('select', () => {
                                                    try {
                                                        const state = mediaUploader.state();
                                                        const selection = state.get('selection');
                                                        if (!selection || selection.length === 0) {
                                                            return;
                                                        }
                                                        const attachment = selection.first().toJSON();
                                                        if (!attachment || !attachment.url) {
                                                            alert('Erreur: L\'image sélectionnée n\'a pas d\'URL valide');
                                                            return;
                                                        }
                                                        // Mettre à jour l'URL
                                                        onChange(element.id, 'src', attachment.url);
                                                        // Optionnellement, mettre à jour les dimensions
                                                        if (!element.width || element.width === 150) {
                                                            onChange(element.id, 'width', attachment.width || 150);
                                                        }
                                                        if (!element.height || element.height === 80) {
                                                            onChange(element.id, 'height', attachment.height || 80);
                                                        }
                                                    }
                                                    catch (error) {
                                                        alert('Erreur: ' + (error instanceof Error ? error.message : 'Erreur inconnue'));
                                                    }
                                                });
                                                mediaUploader.open();
                                            }
                                            catch (error) {
                                                alert('Erreur: ' + (error instanceof Error ? error.message : 'Impossible d\'ouvrir la bibliothèque'));
                                            }
                                        }, style: {
                                            padding: '6px 12px',
                                            backgroundColor: '#007bff',
                                            color: '#fff',
                                            border: 'none',
                                            borderRadius: '4px',
                                            cursor: 'pointer',
                                            fontSize: '11px',
                                            fontWeight: 'bold',
                                            whiteSpace: 'nowrap'
                                        }, title: "S\u00E9lectionner depuis la biblioth\u00E8que WordPress", children: "\uD83D\uDCC1 Choisir" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Texte alternatif" }), (0,jsx_runtime.jsx)("input", { type: "text", value: element.altText || '', onChange: (e) => onChange(element.id, 'altText', e.target.value), placeholder: "Logo de l'entreprise", style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Maintenir les proportions" }), (0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.maintainAspectRatio !== false, onChange: (e) => onChange(element.id, 'maintainAspectRatio', e.target.checked), style: { marginRight: '8px' } }), (0,jsx_runtime.jsx)("span", { style: { fontSize: '11px', color: '#666' }, children: "Pr\u00E9serve le ratio largeur/hauteur" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Afficher une bordure" }), (0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.showBorder !== false, onChange: (e) => onChange(element.id, 'showBorder', e.target.checked), style: { marginRight: '8px' } }), (0,jsx_runtime.jsx)("span", { style: { fontSize: '11px', color: '#666' }, children: "Affiche une bordure autour du logo" })] })] })), logoCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Mode d'ajustement" }), (0,jsx_runtime.jsxs)("select", { value: element.objectFit || 'contain', onChange: (e) => onChange(element.id, 'objectFit', e.target.value), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "contain", children: "Contenir (respecte les proportions)" }), (0,jsx_runtime.jsx)("option", { value: "cover", children: "Couvrir (remplit compl\u00E8tement)" }), (0,jsx_runtime.jsx)("option", { value: "fill", children: "Remplir (\u00E9tire si n\u00E9cessaire)" }), (0,jsx_runtime.jsx)("option", { value: "none", children: "Aucun (taille originale)" }), (0,jsx_runtime.jsx)("option", { value: "scale-down", children: "R\u00E9duire (taille originale ou contenir)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Opacit\u00E9" }), (0,jsx_runtime.jsx)("input", { type: "range", min: "0", max: "1", step: "0.1", value: element.opacity || 1, onChange: (e) => onChange(element.id, 'opacity', parseFloat(e.target.value)), style: {
                                    width: '100%',
                                    marginTop: '4px'
                                } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '11px', color: '#666', textAlign: 'center', marginTop: '2px' }, children: element.opacity || 1 })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Rayon des coins" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", max: "50", value: element.borderRadius || 0, onChange: (e) => onChange(element.id, 'borderRadius', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Rotation" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "-180", max: "180", value: element.rotation || 0, onChange: (e) => onChange(element.id, 'rotation', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '11px', color: '#666', marginTop: '2px' }, children: "Degr\u00E9s" })] })] })), logoCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width || 100, onChange: (e) => onChange(element.id, 'width', parseInt(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height || 100, onChange: (e) => onChange(element.id, 'height', parseInt(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/OrderNumberProperties.tsx


// Composant Accordion personnalisé
const OrderNumberProperties_Accordion = ({ title, children, defaultOpen = false }) => {
    const [isOpen, setIsOpen] = (0,react.useState)(defaultOpen);
    return ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }, children: [(0,jsx_runtime.jsxs)("div", { onClick: () => setIsOpen(!isOpen), style: {
                    padding: '12px',
                    backgroundColor: '#f8f9fa',
                    cursor: 'pointer',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    borderBottom: isOpen ? '1px solid #e9ecef' : 'none'
                }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }, children: title }), (0,jsx_runtime.jsx)("span", { style: {
                            fontSize: '12px',
                            color: '#6c757d',
                            transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)',
                            transition: 'transform 0.2s ease'
                        }, children: "\u25BC" })] }), isOpen && ((0,jsx_runtime.jsx)("div", { style: { padding: '12px', backgroundColor: '#ffffff' }, children: children }))] }));
};
// Composant Toggle personnalisé
const OrderNumberProperties_Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        transition: 'background-color 0.2s ease',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            transition: 'left 0.2s ease',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function OrderNumberProperties({ element, onChange, activeTab, setActiveTab }) {
    const currentTab = activeTab[element.id] || 'fonctionnalites';
    const setCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: currentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: currentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: currentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: currentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: currentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: currentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), currentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Structure des informations" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(OrderNumberProperties_Toggle, { checked: element.showHeaders !== false, onChange: (checked) => onChange(element.id, 'showHeaders', checked), label: "Afficher les en-t\u00EAtes", description: "Affiche les titres des sections" }), (0,jsx_runtime.jsx)(OrderNumberProperties_Toggle, { checked: element.showBackground !== false, onChange: (checked) => onChange(element.id, 'showBackground', checked), label: "Afficher le fond", description: "Affiche un fond color\u00E9 derri\u00E8re le num\u00E9ro de commande" }), (0,jsx_runtime.jsx)(OrderNumberProperties_Toggle, { checked: element.showBorders !== false, onChange: (checked) => onChange(element.id, 'showBorders', checked), label: "Afficher les bordures", description: "Affiche les bordures autour des sections" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "\u00C9l\u00E9ments principaux" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsx)(OrderNumberProperties_Toggle, { checked: element.showLabel !== false, onChange: (checked) => onChange(element.id, 'showLabel', checked), label: "Afficher le libell\u00E9", description: "Affiche un texte devant le num\u00E9ro de commande" }), (0,jsx_runtime.jsx)(OrderNumberProperties_Toggle, { checked: element.showDate !== false, onChange: (checked) => onChange(element.id, 'showDate', checked), label: "Afficher la date", description: "Affiche la date de commande" })] })] }), element.showLabel !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Configuration du libell\u00E9" }), (0,jsx_runtime.jsxs)("div", { style: { paddingLeft: '8px' }, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Texte du libell\u00E9" }), (0,jsx_runtime.jsx)("input", { type: "text", value: element.labelText || 'N° de commande:', onChange: (e) => onChange(element.id, 'labelText', e.target.value), placeholder: "N\u00B0 de commande:", style: {
                                                    width: '100%',
                                                    padding: '6px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '4px',
                                                    fontSize: '12px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '4px' }, children: "Texte affich\u00E9 avant le num\u00E9ro de commande" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position du libell\u00E9" }), (0,jsx_runtime.jsxs)("select", { value: element.labelPosition || 'above', onChange: (e) => onChange(element.id, 'labelPosition', e.target.value), style: {
                                                    width: '100%',
                                                    padding: '6px',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '4px',
                                                    fontSize: '12px'
                                                }, children: [(0,jsx_runtime.jsx)("option", { value: "above", children: "Au-dessus du num\u00E9ro" }), (0,jsx_runtime.jsx)("option", { value: "left", children: "\u00C0 gauche du num\u00E9ro" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "\u00C0 droite du num\u00E9ro" }), (0,jsx_runtime.jsx)("option", { value: "below", children: "En-dessous du num\u00E9ro" })] })] })] })] })), element.showDate !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Format de date" }), (0,jsx_runtime.jsx)("div", { style: { paddingLeft: '8px' }, children: (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Format d'affichage" }), (0,jsx_runtime.jsxs)("select", { value: String(element.dateFormat || 'DD/MM/YYYY'), onChange: (e) => onChange(element.id, 'dateFormat', e.target.value), style: {
                                                width: '100%',
                                                padding: '6px',
                                                border: '1px solid #ccc',
                                                borderRadius: '4px',
                                                fontSize: '12px'
                                            }, children: [(0,jsx_runtime.jsx)("option", { value: "DD/MM/YYYY", children: "JJ/MM/AAAA (31/12/2024)" }), (0,jsx_runtime.jsx)("option", { value: "MM/DD/YYYY", children: "MM/JJ/AAAA (12/31/2024)" }), (0,jsx_runtime.jsx)("option", { value: "DD-MM-YYYY", children: "JJ-MM-AAAA (31-12-2024)" }), (0,jsx_runtime.jsx)("option", { value: "YYYY-MM-DD", children: "AAAA-MM-JJ (2024-12-31)" }), (0,jsx_runtime.jsx)("option", { value: "DD MMM YYYY", children: "JJ MMM AAAA (31 d\u00E9c. 2024)" })] }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '4px' }, children: "Format d'affichage de la date de commande" })] }) })] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }, children: [(0,jsx_runtime.jsx)("div", { style: { fontSize: '12px', fontWeight: 'bold', marginBottom: '8px', color: '#495057' }, children: "\u2139\uFE0F Information" }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '11px', color: '#6c757d', lineHeight: '1.4' }, children: "Le num\u00E9ro de commande est automatiquement r\u00E9cup\u00E9r\u00E9 depuis WooCommerce. Le format et la num\u00E9rotation sont g\u00E9r\u00E9s par votre configuration WooCommerce." })] })] })), currentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)(OrderNumberProperties_Accordion, { title: "Th\u00E8mes pr\u00E9d\u00E9finis", defaultOpen: true, children: (0,jsx_runtime.jsx)("div", { style: {
                                display: 'grid',
                                gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
                                gap: '8px',
                                maxHeight: '200px',
                                overflowY: 'auto',
                                padding: '4px',
                                border: '1px solid #e0e0e0',
                                borderRadius: '4px',
                                backgroundColor: '#fafafa'
                            }, children: [
                                {
                                    id: 'clean',
                                    name: 'Propre',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #f3f4f6',
                                            borderRadius: '4px',
                                            backgroundColor: '#ffffff',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '1px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '2px',
                                                    backgroundColor: '#f9fafb'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '2px',
                                                    backgroundColor: '#ffffff'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '2px',
                                                    backgroundColor: '#f9fafb'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#ffffff',
                                        borderColor: '#f3f4f6',
                                        textColor: '#374151',
                                        headerTextColor: '#111827',
                                        showHeaders: true,
                                        showBackground: true,
                                        showBorders: true
                                    }
                                },
                                {
                                    id: 'subtle',
                                    name: 'Discret',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #f1f5f9',
                                            borderRadius: '6px',
                                            backgroundColor: '#fafbfc',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#e2e8f0',
                                                    borderRadius: '1px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#fafbfc',
                                                    borderRadius: '1px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#e2e8f0',
                                                    borderRadius: '1px'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#fafbfc',
                                        borderColor: '#f1f5f9',
                                        textColor: '#475569',
                                        headerTextColor: '#334155',
                                        showHeaders: true,
                                        showBackground: true,
                                        showBorders: true
                                    }
                                },
                                {
                                    id: 'elegant',
                                    name: 'Élégant',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '2px solid #f3e8ff',
                                            borderRadius: '8px',
                                            backgroundColor: '#fefefe',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#f3e8ff',
                                                    borderRadius: '2px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#fefefe',
                                                    borderRadius: '2px'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#f3e8ff',
                                                    borderRadius: '2px'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#fefefe',
                                        borderColor: '#f3e8ff',
                                        textColor: '#6b21a8',
                                        headerTextColor: '#581c87',
                                        showHeaders: true,
                                        showBackground: true,
                                        showBorders: true
                                    }
                                },
                                {
                                    id: 'corporate',
                                    name: 'Corporate',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: '1px solid #e5e7eb',
                                            borderRadius: '0px',
                                            backgroundColor: '#ffffff',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '2px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '3px',
                                                    backgroundColor: '#f3f4f6'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '3px',
                                                    backgroundColor: '#ffffff'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '3px',
                                                    backgroundColor: '#f3f4f6'
                                                } })] })),
                                    styles: {
                                        backgroundColor: '#ffffff',
                                        borderColor: '#e5e7eb',
                                        textColor: '#374151',
                                        headerTextColor: '#111827',
                                        showHeaders: true,
                                        showBackground: false,
                                        showBorders: true
                                    }
                                },
                                {
                                    id: 'minimal',
                                    name: 'Minimal',
                                    preview: ((0,jsx_runtime.jsxs)("div", { style: {
                                            width: '100%',
                                            height: '35px',
                                            border: 'none',
                                            borderRadius: '0px',
                                            backgroundColor: 'transparent',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            gap: '1px'
                                        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                    width: '90%',
                                                    height: '1px',
                                                    backgroundColor: '#e5e7eb'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '75%',
                                                    height: '1px',
                                                    backgroundColor: 'transparent'
                                                } }), (0,jsx_runtime.jsx)("div", { style: {
                                                    width: '60%',
                                                    height: '1px',
                                                    backgroundColor: '#e5e7eb'
                                                } })] })),
                                    styles: {
                                        backgroundColor: 'transparent',
                                        borderColor: 'transparent',
                                        textColor: '#6b7280',
                                        headerTextColor: '#374151',
                                        showHeaders: false,
                                        showBackground: false,
                                        showBorders: false
                                    }
                                }
                            ].map(theme => ((0,jsx_runtime.jsxs)("button", { onClick: () => {
                                    // Appliquer toutes les propriétés du thème
                                    Object.entries(theme.styles).forEach(([property, value]) => {
                                        onChange(element.id, property, value);
                                    });
                                }, style: {
                                    padding: '6px',
                                    border: '2px solid transparent',
                                    borderRadius: '6px',
                                    backgroundColor: '#ffffff',
                                    cursor: 'pointer',
                                    textAlign: 'center',
                                    transition: 'all 0.2s ease',
                                    minHeight: '70px',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    alignItems: 'center',
                                    gap: '4px'
                                }, onMouseEnter: (e) => {
                                    e.currentTarget.style.borderColor = '#007bff';
                                    e.currentTarget.style.backgroundColor = '#f8f9fa';
                                    e.currentTarget.style.transform = 'translateY(-1px)';
                                }, onMouseLeave: (e) => {
                                    e.currentTarget.style.borderColor = 'transparent';
                                    e.currentTarget.style.backgroundColor = '#ffffff';
                                    e.currentTarget.style.transform = 'translateY(0)';
                                }, title: `Appliquer le thème ${theme.name}`, children: [(0,jsx_runtime.jsx)("div", { style: {
                                            fontSize: '10px',
                                            fontWeight: 'bold',
                                            color: '#333',
                                            textAlign: 'center',
                                            lineHeight: '1.2'
                                        }, children: theme.name }), theme.preview] }, theme.id))) }) }), (0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), element.showLabel !== false && element.showHeaders !== false && ((0,jsx_runtime.jsxs)(OrderNumberProperties_Accordion, { title: "Police du libell\u00E9", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "32", value: element.headerFontSize || (Number(element.fontSize) || 14) + 2, onChange: (e) => onChange(element.id, 'headerFontSize', parseInt(e.target.value) || 16), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police" }), (0,jsx_runtime.jsxs)("select", { value: String(element.headerFontFamily || element.fontFamily || 'Arial'), onChange: (e) => onChange(element.id, 'headerFontFamily', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontWeight || element.fontWeight || 'bold', onChange: (e) => onChange(element.id, 'headerFontWeight', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.headerFontStyle || element.fontStyle || 'normal', onChange: (e) => onChange(element.id, 'headerFontStyle', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de police" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerTextColor || element.textColor || '#111827', onChange: (e) => onChange(element.id, 'headerTextColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] })), (0,jsx_runtime.jsxs)(OrderNumberProperties_Accordion, { title: "Police du num\u00E9ro et de la date", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police du num\u00E9ro" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.numberFontSize || element.bodyFontSize || element.fontSize || 14, onChange: (e) => onChange(element.id, 'numberFontSize', parseInt(e.target.value) || 14), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police de la date" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "24", value: element.dateFontSize || (Number(element.bodyFontSize || element.fontSize) || 14) - 2, onChange: (e) => onChange(element.id, 'dateFontSize', parseInt(e.target.value) || 12), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Famille de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontFamily || element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'bodyFontFamily', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" }), (0,jsx_runtime.jsx)("option", { value: "Trebuchet MS", children: "Trebuchet MS" }), (0,jsx_runtime.jsx)("option", { value: "Calibri", children: "Calibri" }), (0,jsx_runtime.jsx)("option", { value: "Cambria", children: "Cambria" }), (0,jsx_runtime.jsx)("option", { value: "Segoe UI", children: "Segoe UI" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontWeight || element.fontWeight || 'normal', onChange: (e) => onChange(element.id, 'bodyFontWeight', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal (400)" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras (700)" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin (300)" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras (900)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.bodyFontStyle || element.fontStyle || 'normal', onChange: (e) => onChange(element.id, 'bodyFontStyle', e.target.value), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "italic", children: "Italique" }), (0,jsx_runtime.jsx)("option", { value: "oblique", children: "Oblique" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de police" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#374151', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] }), (0,jsx_runtime.jsxs)(OrderNumberProperties_Accordion, { title: "Couleurs", defaultOpen: false, children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte du libell\u00E9" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.headerTextColor || element.textColor || '#111827', onChange: (e) => onChange(element.id, 'headerTextColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte du num\u00E9ro" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#374151', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] }), element.showBackground !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor === 'transparent' ? '#ffffff' : (element.backgroundColor || '#ffffff'), onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '0' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur des bordures" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.borderColor || '#e5e7eb', onChange: (e) => onChange(element.id, 'borderColor', e.target.value), style: {
                                            width: '100%',
                                            height: '32px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px'
                                        } })] })] })] })), currentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width || 200, onChange: (e) => onChange(element.id, 'width', parseInt(e.target.value) || 200), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height || 40, onChange: (e) => onChange(element.id, 'height', parseInt(e.target.value) || 40), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement du contenu" }), (0,jsx_runtime.jsxs)("select", { value: element.contentAlign || 'left', onChange: (e) => onChange(element.id, 'contentAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Aligner \u00E0 gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centrer" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Aligner \u00E0 droite" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement horizontal" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/DynamicTextProperties.tsx

// Composant Toggle personnalisé
const DynamicTextProperties_Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        transition: 'background-color 0.2s ease',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            transition: 'left 0.2s ease',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function DynamicTextProperties({ element, onChange, activeTab, setActiveTab }) {
    var _a;
    const dynamicCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setDynamicCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    // Liste des exemples prédéfinis
    const textExamples = [
        // === PROFESSIONNEL ===
        {
            id: 'signature_zone',
            label: 'Zone de signature',
            template: 'Signature du client :\n\n______________________________\n\nDate :\n\n____/____/________'
        },
        {
            id: 'signature_client',
            label: 'Signature client',
            template: 'Signature du client\n\n______________________________\n\nNom et prénom :\n\nDate : ____/____/________'
        },
        {
            id: 'signature_entreprise',
            label: 'Signature entreprise',
            template: 'Pour l\'entreprise :\n\n______________________________\n\n[prenom] [nom]\n[entreprise]'
        },
        {
            id: 'signature_double',
            label: 'Double signature',
            template: 'Signature du client :\n______________________________\n\nSignature de l\'entreprise :\n______________________________\n\nDate : ____/____/________'
        },
        {
            id: 'signature_with_stamp',
            label: 'Signature avec cachet',
            template: 'Signature et cachet :\n\n______________________________\n\n[entreprise]\nCachet de l\'entreprise\n\nDate : ____/____/________'
        },
        {
            id: 'signature_contract',
            label: 'Signature contrat',
            template: 'Fait à [Ville], le [date]\n\nSignatures :\n\nLe Client :\n______________________________\n\nL\'Entreprise :\n______________________________\n\n[prenom] [nom]'
        },
        {
            id: 'signature_approval',
            label: 'Approbation',
            template: 'Lu et approuvé :\n\n______________________________\n\n[prenom] [nom]\n[date]'
        },
        {
            id: 'signature_witness',
            label: 'Témoin',
            template: 'Témoin :\n\n______________________________\n\nNom et prénom :\n\nAdresse :\n\nDate : ____/____/________'
        },
        {
            id: 'document_title',
            label: 'Titre de document',
            template: 'CONTRAT DE PRESTATION DE SERVICES'
        },
        {
            id: 'document_subtitle',
            label: 'Sous-titre de document',
            template: 'Entre les parties ci-dessous désignées'
        },
        // === FORMULAIRES ===
        {
            id: 'checkbox_terms',
            label: 'Case - Conditions générales',
            template: '☐ J\'ai lu et j\'accepte les conditions générales de vente'
        },
        {
            id: 'checkbox_privacy',
            label: 'Case - Politique de confidentialité',
            template: '☐ J\'accepte la politique de confidentialité'
        },
        {
            id: 'checkbox_newsletter',
            label: 'Case - Newsletter',
            template: '☐ Je souhaite m\'inscrire à la newsletter'
        },
        {
            id: 'checkbox_certification',
            label: 'Case - Certification',
            template: '☐ Je certifie l\'exactitude des informations fournies'
        },
        {
            id: 'checkbox_quote_acceptance',
            label: 'Case - Acceptation devis',
            template: '☐ J\'accepte ce devis et souhaite passer commande'
        },
        {
            id: 'checkbox_order_confirmation',
            label: 'Case - Confirmation commande',
            template: '☐ Je confirme ma commande selon les termes du devis'
        },
        {
            id: 'checkbox_payment_terms',
            label: 'Case - Conditions de paiement',
            template: '☐ J\'accepte les conditions de paiement proposées'
        },
        {
            id: 'checkbox_delivery_terms',
            label: 'Case - Conditions de livraison',
            template: '☐ J\'accepte les conditions de livraison et délais'
        },
        {
            id: 'checkbox_price_acceptance',
            label: 'Case - Acceptation tarif',
            template: '☐ J\'accepte le tarif proposé pour cette prestation'
        },
        {
            id: 'checkbox_liability_waiver',
            label: 'Case - Décharge responsabilité',
            template: '☐ Je dégage l\'entreprise de toute responsabilité en cas de...'
        },
        {
            id: 'checkbox_data_processing',
            label: 'Case - Traitement données',
            template: '☐ J\'autorise le traitement de mes données personnelles'
        },
        {
            id: 'checkbox_contract_acceptance',
            label: 'Case - Acceptation contrat',
            template: '☐ J\'accepte les termes du contrat ci-dessus'
        },
        // === INFORMATIONS ===
        {
            id: 'contact_complete',
            label: 'Contact complet',
            template: '[entreprise]\n[Adresse ligne 1]\n[Code postal] [Ville]\n[telephone] | [email]\n[site]'
        },
        {
            id: 'contact_minimal',
            label: 'Contact minimal',
            template: '[email] | [telephone]'
        },
        {
            id: 'legal_mentions',
            label: 'Mentions légales',
            template: 'SIRET: [SIRET] | TVA: [TVA]\nCapital social: [capital]€'
        },
        // === DATES ===
        {
            id: 'date_today',
            label: 'Date du jour',
            template: 'Fait à [Ville], le [date]'
        },
        {
            id: 'date_contract',
            label: 'Date de contrat',
            template: 'Contrat établi le [date]'
        },
        // === TEXTES ===
        {
            id: 'paragraph_intro',
            label: 'Introduction',
            template: 'Par la présente, il est convenu ce qui suit entre les parties :'
        },
        {
            id: 'paragraph_conclusion',
            label: 'Conclusion',
            template: 'Les parties déclarent avoir lu et approuvé l\'intégralité du présent document.'
        },
        {
            id: 'paragraph_payment',
            label: 'Paiement',
            template: 'Le paiement s\'effectuera selon les modalités suivantes :'
        },
        // === LISTES ===
        {
            id: 'bullet_services',
            label: 'Services (liste)',
            template: '• Conseil et accompagnement\n• Formation personnalisée\n• Support technique'
        },
        {
            id: 'numbered_steps',
            label: 'Étapes (liste)',
            template: '1. Validation du cahier des charges\n2. Réalisation du projet\n3. Livraison et recette'
        },
        {
            id: 'bullet_features',
            label: 'Fonctionnalités',
            template: '• Interface intuitive\n• Performance optimisée\n• Sécurité renforcée'
        },
        // === PROFESSIONNEL AVANCÉ ===
        {
            id: 'confidentiality_clause',
            label: 'Clause de confidentialité',
            template: 'Les parties s\'engagent à garder confidentielles les informations échangées.'
        },
        {
            id: 'jurisdiction_clause',
            label: 'Clause de juridiction',
            template: 'Tout litige sera soumis aux tribunaux de [Ville].'
        },
        {
            id: 'termination_clause',
            label: 'Clause de résiliation',
            template: 'Le présent contrat peut être résilié par l\'une ou l\'autre des parties avec un préavis de 30 jours.'
        },
        {
            id: 'custom',
            label: 'Personnalisé',
            template: ''
        }
    ];
    // Détecter le template actuel
    const currentText = element.text || '';
    const currentTemplate = element.textTemplate ||
        ((_a = textExamples.find((ex) => ex.template === currentText)) === null || _a === void 0 ? void 0 : _a.id) || 'custom';
    // Fonction pour changer de template
    const handleTemplateChange = (templateId) => {
        const selectedExample = textExamples.find((ex) => ex.id === templateId);
        if (selectedExample) {
            onChange(element.id, 'text', selectedExample.template);
            onChange(element.id, 'textTemplate', selectedExample.id);
        }
    };
    const dynamicTextThemes = [
        {
            id: 'clean',
            name: 'Propre',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #f3f4f6',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '2px',
                            backgroundColor: '#f9fafb'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#ffffff'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#f9fafb'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#f3f4f6',
                textColor: '#374151',
                headerTextColor: '#111827'
            }
        },
        {
            id: 'subtle',
            name: 'Discret',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #e5e7eb',
                    borderRadius: '4px',
                    backgroundColor: '#f9fafb',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '2px',
                            backgroundColor: '#e5e7eb'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#f3f4f6'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#e5e7eb'
                        } })] })),
            styles: {
                backgroundColor: '#f9fafb',
                borderColor: '#e5e7eb',
                textColor: '#6b7280',
                headerTextColor: '#374151'
            }
        },
        {
            id: 'highlighted',
            name: 'Surligné',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #dbeafe',
                    borderRadius: '4px',
                    backgroundColor: '#eff6ff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '2px',
                            backgroundColor: '#dbeafe'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '2px',
                            backgroundColor: '#eff6ff'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#dbeafe'
                        } })] })),
            styles: {
                backgroundColor: '#eff6ff',
                borderColor: '#dbeafe',
                textColor: '#1e40af',
                headerTextColor: '#1e3a8a'
            }
        }
    ];
    const availableVariables = [
        { key: '[date]', label: 'Date actuelle' },
        { key: '[nom]', label: 'Nom' },
        { key: '[prenom]', label: 'Prénom' },
        { key: '[entreprise]', label: 'Nom de l\'entreprise' },
        { key: '[telephone]', label: 'Téléphone' },
        { key: '[email]', label: 'Email' },
        { key: '[site]', label: 'Site web' },
        { key: '[ville]', label: 'Ville' },
        { key: '[siret]', label: 'Numéro SIRET' },
        { key: '[tva]', label: 'Numéro TVA' },
        { key: '[capital]', label: 'Capital social' },
        { key: '[rcs]', label: 'RCS' }
    ];
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setDynamicCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: dynamicCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: dynamicCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setDynamicCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: dynamicCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: dynamicCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setDynamicCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: dynamicCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: dynamicCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), dynamicCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Mod\u00E8le de texte" }), (0,jsx_runtime.jsx)("select", { value: currentTemplate, onChange: (e) => handleTemplateChange(e.target.value), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: textExamples.map((example) => ((0,jsx_runtime.jsx)("option", { value: example.id, children: example.label }, example.id))) })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Texte personnalis\u00E9" }), (0,jsx_runtime.jsx)("textarea", { value: element.text || '', onChange: (e) => onChange(element.id, 'text', e.target.value), placeholder: "Modifiez le texte ou utilisez les variables disponibles", rows: 3, style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px',
                                    resize: 'vertical'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }, children: "Variables disponibles" }), (0,jsx_runtime.jsx)("div", { style: {
                                    maxHeight: '150px',
                                    overflowY: 'auto',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    padding: '8px',
                                    backgroundColor: '#fafafa'
                                }, children: availableVariables.map((variable) => ((0,jsx_runtime.jsxs)("div", { onClick: () => {
                                        const currentText = element.text || '';
                                        const newText = currentText + variable.key;
                                        onChange(element.id, 'text', newText);
                                    }, style: {
                                        cursor: 'pointer',
                                        padding: '4px 8px',
                                        marginBottom: '4px',
                                        backgroundColor: '#ffffff',
                                        border: '1px solid #e0e0e0',
                                        borderRadius: '3px',
                                        fontSize: '11px',
                                        fontFamily: 'monospace'
                                    }, title: `Cliquez pour insérer ${variable.key}`, children: [(0,jsx_runtime.jsx)("strong", { children: variable.key }), " - ", variable.label] }, variable.key))) })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Retour \u00E0 la ligne automatique" }), (0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.autoWrap !== false, onChange: (e) => onChange(element.id, 'autoWrap', e.target.checked), style: { marginRight: '8px' } }), (0,jsx_runtime.jsx)("span", { style: { fontSize: '11px', color: '#666' }, children: "Adapte le texte \u00E0 la largeur" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Affichage du fond" }), (0,jsx_runtime.jsx)("div", { style: { paddingLeft: '8px' }, children: (0,jsx_runtime.jsx)(DynamicTextProperties_Toggle, { checked: element.showBackground !== false, onChange: (checked) => onChange(element.id, 'showBackground', checked), label: "Afficher le fond", description: "Affiche un fond color\u00E9 derri\u00E8re le texte dynamique" }) })] })] })), dynamicCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }, children: "Th\u00E8me visuel" }), (0,jsx_runtime.jsx)("div", { style: {
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
                                    gap: '8px',
                                    maxHeight: '200px',
                                    overflowY: 'auto',
                                    padding: '4px',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    backgroundColor: '#fafafa'
                                }, children: dynamicTextThemes.map((theme) => ((0,jsx_runtime.jsxs)("div", { onClick: () => onChange(element.id, 'theme', theme.id), style: {
                                        cursor: 'pointer',
                                        border: element.theme === theme.id ? '2px solid #007bff' : '2px solid transparent',
                                        borderRadius: '6px',
                                        padding: '6px',
                                        backgroundColor: '#ffffff',
                                        transition: 'all 0.2s ease'
                                    }, title: theme.name, children: [(0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', fontWeight: 'bold', marginBottom: '4px', textAlign: 'center' }, children: theme.name }), theme.preview] }, theme.id))) })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Taille du texte" }), (0,jsx_runtime.jsxs)("select", { value: element.fontSize || '12', onChange: (e) => onChange(element.id, 'fontSize', e.target.value), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "10", children: "Petit (10px)" }), (0,jsx_runtime.jsx)("option", { value: "12", children: "Normal (12px)" }), (0,jsx_runtime.jsx)("option", { value: "14", children: "Moyen (14px)" }), (0,jsx_runtime.jsx)("option", { value: "16", children: "Grand (16px)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Alignement du texte" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" }), (0,jsx_runtime.jsx)("option", { value: "justify", children: "Justifi\u00E9" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Style du texte" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsxs)("label", { style: { fontSize: '11px', display: 'flex', alignItems: 'center' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.fontWeight === 'bold', onChange: (e) => onChange(element.id, 'fontWeight', e.target.checked ? 'bold' : 'normal'), style: { marginRight: '4px' } }), "Gras"] }), (0,jsx_runtime.jsxs)("label", { style: { fontSize: '11px', display: 'flex', alignItems: 'center' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.fontStyle === 'italic', onChange: (e) => onChange(element.id, 'fontStyle', e.target.checked ? 'italic' : 'normal'), style: { marginRight: '4px' } }), "Italique"] }), (0,jsx_runtime.jsxs)("label", { style: { fontSize: '11px', display: 'flex', alignItems: 'center' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.textDecoration === 'underline', onChange: (e) => onChange(element.id, 'textDecoration', e.target.checked ? 'underline' : 'none'), style: { marginRight: '4px' } }), "Soulign\u00E9"] })] })] }), element.showBackground !== false && ((0,jsx_runtime.jsx)("div", { style: { marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }, children: (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor || '#e5e7eb', onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                        width: '100%',
                                        height: '40px',
                                        border: '1px solid #d1d5db',
                                        borderRadius: '6px',
                                        cursor: 'pointer'
                                    } })] }) }))] })), dynamicCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width || 200, onChange: (e) => onChange(element.id, 'width', parseInt(e.target.value) || 200), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height || 50, onChange: (e) => onChange(element.id, 'height', parseInt(e.target.value) || 50), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/MentionsProperties.tsx

// Composant Toggle personnalisé
const MentionsProperties_Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        transition: 'background-color 0.2s ease',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            transition: 'left 0.2s ease',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function MentionsProperties({ element, onChange, activeTab, setActiveTab }) {
    var _a, _b, _c, _d, _e, _f;
    const mentionsCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setMentionsCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    const mentionsThemes = [
        {
            id: 'legal',
            name: 'Légal',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #6b7280',
                    borderRadius: '4px',
                    backgroundColor: '#ffffff',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '2px',
                            backgroundColor: '#6b7280'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '1px',
                            backgroundColor: '#9ca3af'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '1px',
                            backgroundColor: '#d1d5db'
                        } })] })),
            styles: {
                backgroundColor: '#ffffff',
                borderColor: '#6b7280',
                textColor: '#374151',
                headerTextColor: '#111827'
            }
        },
        {
            id: 'subtle',
            name: 'Discret',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: '1px solid #e5e7eb',
                    borderRadius: '4px',
                    backgroundColor: '#f9fafb',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '1px',
                            backgroundColor: '#9ca3af'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '1px',
                            backgroundColor: '#d1d5db'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '1px',
                            backgroundColor: '#e5e7eb'
                        } })] })),
            styles: {
                backgroundColor: '#f9fafb',
                borderColor: '#e5e7eb',
                textColor: '#6b7280',
                headerTextColor: '#374151'
            }
        },
        {
            id: 'minimal',
            name: 'Minimal',
            preview: ((0,jsx_runtime.jsxs)("div", { style: {
                    width: '100%',
                    height: '35px',
                    border: 'none',
                    borderRadius: '4px',
                    backgroundColor: 'transparent',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    gap: '1px'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            width: '90%',
                            height: '1px',
                            backgroundColor: '#d1d5db'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '75%',
                            height: '1px',
                            backgroundColor: '#e5e7eb'
                        } }), (0,jsx_runtime.jsx)("div", { style: {
                            width: '60%',
                            height: '1px',
                            backgroundColor: '#f3f4f6'
                        } })] })),
            styles: {
                backgroundColor: 'transparent',
                borderColor: 'transparent',
                textColor: '#6b7280',
                headerTextColor: '#374151'
            }
        }
    ];
    const predefinedMentions = [
        {
            key: 'cgv',
            label: 'Conditions Générales de Vente',
            text: 'Conditions Générales de Vente applicables. Consultez notre site web pour plus de détails.'
        },
        {
            key: 'legal',
            label: 'Mentions légales',
            text: 'Document établi sous la responsabilité de l\'entreprise. Toutes les informations sont confidentielles.'
        },
        {
            key: 'payment',
            label: 'Conditions de paiement',
            text: 'Paiement dû dans les délais convenus. Tout retard peut entraîner des pénalités.'
        },
        {
            key: 'warranty',
            label: 'Garantie',
            text: 'Garantie légale de conformité et garantie contre les vices cachés selon les articles L217-4 et suivants du Code de la consommation.'
        },
        {
            key: 'returns',
            label: 'Droit de rétractation',
            text: 'Droit de rétractation de 14 jours selon l\'article L221-18 du Code de la consommation.'
        },
        {
            key: 'tva',
            label: 'TVA et mentions fiscales',
            text: 'TVA non applicable, art. 293 B du CGI. Mention : auto-entrepreneur soumise à l\'impôt sur le revenu.'
        },
        {
            key: 'penalties',
            label: 'Pénalités de retard',
            text: 'Tout retard de paiement donnera lieu au paiement d\'une pénalité égale à 3 fois le taux d\'intérêt légal en vigueur.'
        },
        {
            key: 'property',
            label: 'Réserve de propriété',
            text: 'Les biens vendus restent la propriété du vendeur jusqu\'au paiement intégral du prix.'
        },
        {
            key: 'jurisdiction',
            label: 'Juridiction compétente',
            text: 'Tout litige sera soumis à la compétence exclusive des tribunaux de commerce français.'
        },
        {
            key: 'rgpd',
            label: 'RGPD - Protection des données',
            text: 'Vos données personnelles sont traitées conformément au RGPD. Consultez notre politique de confidentialité.'
        },
        {
            key: 'discount',
            label: 'Escompte',
            text: 'Escompte pour paiement anticipé : 2% du montant HT si paiement sous 8 jours.'
        },
        {
            key: 'clause',
            label: 'Clause de réserve',
            text: 'Sous réserve d\'acceptation de votre commande et de disponibilité des produits.'
        },
        {
            key: 'intellectual',
            label: 'Propriété intellectuelle',
            text: 'Tous droits de propriété intellectuelle réservés. Reproduction interdite sans autorisation.'
        },
        {
            key: 'force',
            label: 'Force majeure',
            text: 'Aucun des parties ne pourra être tenu responsable en cas de force majeure.'
        },
        {
            key: 'liability',
            label: 'Limitation de responsabilité',
            text: 'Notre responsabilité est limitée à la valeur de la commande en cas de faute prouvée.'
        },
        {
            key: 'tva_info',
            label: 'Informations TVA',
            text: 'TVA non applicable - article 293 B du CGI. Régime micro-entreprise.'
        },
        {
            key: 'rcs_info',
            label: 'Informations RCS',
            text: 'RCS Paris 123 456 789 - SIRET 123 456 789 00012 - APE 1234Z'
        },
        {
            key: 'siret_info',
            label: 'Informations SIRET',
            text: 'SIRET 123 456 789 00012 - NAF 1234Z - TVA FR 12 345 678 901'
        },
        {
            key: 'legal_status',
            label: 'Statut juridique',
            text: 'Société à responsabilité limitée au capital de 10 000€ - RCS Paris 123 456 789'
        },
        {
            key: 'insurance',
            label: 'Assurance responsabilité',
            text: 'Couvert par assurance responsabilité civile professionnelle - Police N° 123456789'
        },
        {
            key: 'mediation',
            label: 'Médiation consommateur',
            text: 'En cas de litige, le consommateur peut saisir gratuitement le médiateur compétent.'
        },
        {
            key: 'iban',
            label: 'Coordonnées bancaires',
            text: 'IBAN FR76 1234 5678 9012 3456 7890 123 - BIC BNPAFRPP'
        },
        {
            key: 'delivery',
            label: 'Conditions de livraison',
            text: 'Livraison sous 3-5 jours ouvrés. Frais de port offerts à partir de 50€ HT.'
        },
        {
            key: 'packaging',
            label: 'Emballage et environnement',
            text: 'Emballages recyclables. Respectueux de l\'environnement.'
        },
        {
            key: 'medley',
            label: 'Médley (Combinaison)',
            text: ''
        },
        {
            key: 'custom',
            label: 'Personnalisé',
            text: ''
        }
    ];
    // Détecter automatiquement le type de mention basé sur le texte actuel
    const detectMentionType = () => {
        const currentText = element.text || '';
        const currentMentionType = element.mentionType || 'custom';
        // Si un type est déjà défini et que ce n'est pas custom, le garder
        if (currentMentionType && currentMentionType !== 'custom') {
            return currentMentionType;
        }
        // Pour le medley, vérifier s'il y a des mentions sélectionnées
        if (element.selectedMentions && element.selectedMentions.length > 0) {
            return 'medley';
        }
        // Sinon, essayer de détecter automatiquement
        const matchingMention = predefinedMentions.find(mention => mention.key !== 'custom' && mention.key !== 'medley' && mention.text === currentText);
        return matchingMention ? matchingMention.key : 'custom';
    };
    const currentMentionType = detectMentionType();
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setMentionsCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: mentionsCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: mentionsCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setMentionsCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: mentionsCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: mentionsCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setMentionsCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: mentionsCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: mentionsCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), mentionsCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Type de mentions" }), (0,jsx_runtime.jsx)("select", { value: currentMentionType, onChange: (e) => {
                                    const selectedMention = predefinedMentions.find(m => m.key === e.target.value);
                                    onChange(element.id, 'mentionType', e.target.value);
                                    // Ne mettre à jour le texte que si ce n'est pas "custom" et qu'il y a du texte prédéfini
                                    if (selectedMention && selectedMention.key !== 'custom' && selectedMention.key !== 'medley' && selectedMention.text) {
                                        onChange(element.id, 'text', selectedMention.text);
                                    }
                                    // Pour "custom" et "medley", on garde le texte actuel
                                }, style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: predefinedMentions.map((mention) => ((0,jsx_runtime.jsx)("option", { value: mention.key, children: mention.label }, mention.key))) })] }), currentMentionType === 'medley' && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px', padding: '12px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#f9f9f9' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }, children: "S\u00E9lectionnez les mentions \u00E0 combiner :" }), (0,jsx_runtime.jsx)("div", { style: {
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                                    gap: '6px',
                                    maxHeight: '200px',
                                    overflowY: 'auto'
                                }, children: predefinedMentions.filter(m => m.key !== 'medley' && m.key !== 'custom').map((mention) => {
                                    const selectedMentions = element.selectedMentions || [];
                                    const isSelected = selectedMentions.includes(mention.key);
                                    return ((0,jsx_runtime.jsxs)("label", { style: {
                                            display: 'flex',
                                            alignItems: 'flex-start',
                                            fontSize: '11px',
                                            cursor: 'pointer',
                                            padding: '4px',
                                            borderRadius: '3px',
                                            backgroundColor: isSelected ? '#e3f2fd' : 'transparent'
                                        }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: isSelected, onChange: (e) => {
                                                    const currentSelected = element.selectedMentions || [];
                                                    let newSelected;
                                                    if (e.target.checked) {
                                                        newSelected = [...currentSelected, mention.key];
                                                    }
                                                    else {
                                                        newSelected = currentSelected.filter((key) => key !== mention.key);
                                                    }
                                                    onChange(element.id, 'selectedMentions', newSelected);
                                                    // Générer le texte combiné avec le séparateur configuré
                                                    const separatorMap = {
                                                        'double_newline': '\n\n',
                                                        'single_newline': '\n',
                                                        'dash': ' - ',
                                                        'bullet': ' • ',
                                                        'pipe': ' | '
                                                    };
                                                    const separator = separatorMap[(element.medleySeparator || 'double_newline')] || '\n\n';
                                                    const combinedText = newSelected
                                                        .map((key) => { var _a; return (_a = predefinedMentions.find(m => m.key === key)) === null || _a === void 0 ? void 0 : _a.text; })
                                                        .filter(Boolean)
                                                        .join(separator);
                                                    onChange(element.id, 'text', combinedText);
                                                    // Ajuster automatiquement la hauteur et la largeur selon le contenu
                                                    const lines = combinedText.split('\n');
                                                    const fontSize = typeof element.fontSize === 'string' ? parseFloat(element.fontSize) : (element.fontSize || 10);
                                                    const lineHeight = fontSize * 1.3; // Harmoniser avec pdf-canvas-core.js
                                                    const padding = 10; // Réduire pour cohérence
                                                    const iconSpace = 20; // Espace pour l'icône
                                                    const minHeight = 60; // Hauteur minimale basée sur la valeur par défaut
                                                    const calculatedHeight = Math.max(minHeight, lines.length * lineHeight + iconSpace + padding);
                                                    const maxHeight = 500;
                                                    const newHeight = Math.min(calculatedHeight, maxHeight);
                                                    // Calculer la largeur basée sur la ligne la plus longue
                                                    const canvas = document.createElement('canvas');
                                                    const ctx = canvas.getContext('2d');
                                                    if (ctx) {
                                                        ctx.font = `${element.fontWeight || 'normal'} ${fontSize}px ${element.fontFamily || 'Arial'}`;
                                                        const maxLineWidth = Math.max(...lines.map((line) => ctx.measureText(line).width));
                                                        const margin = 20; // Même marge qu dans pdf-canvas-core.js (width - 20)
                                                        const minWidth = 500; // Largeur minimale basée sur la valeur par défaut
                                                        const calculatedWidth = Math.max(minWidth, maxLineWidth + margin);
                                                        const maxWidth = 800; // Largeur maximale
                                                        const newWidth = Math.min(calculatedWidth, maxWidth);
                                                        if (element.width !== newWidth) {
                                                            onChange(element.id, 'width', newWidth);
                                                        }
                                                    }
                                                    if (element.height !== newHeight) {
                                                        onChange(element.id, 'height', newHeight);
                                                    }
                                                }, style: { marginRight: '6px', marginTop: '1px' } }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("div", { style: { fontWeight: 'bold', marginBottom: '2px' }, children: mention.label }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', lineHeight: '1.3' }, children: mention.text.length > 60 ? mention.text.substring(0, 60) + '...' : mention.text })] })] }, mention.key));
                                }) }), (0,jsx_runtime.jsxs)("div", { style: { marginTop: '8px', fontSize: '10px', color: '#666' }, children: [((_b = (_a = element.selectedMentions) === null || _a === void 0 ? void 0 : _a.length) !== null && _b !== void 0 ? _b : 0), " mention(s) s\u00E9lectionn\u00E9e(s)", ((_d = (_c = element.selectedMentions) === null || _c === void 0 ? void 0 : _c.length) !== null && _d !== void 0 ? _d : 0) > 0 && ((0,jsx_runtime.jsx)("span", { style: { color: '#007bff', marginLeft: '8px' }, children: "\u2022 Dimensions ajustables manuellement (avec clipping)" }))] }), ((_f = (_e = element.selectedMentions) === null || _e === void 0 ? void 0 : _e.length) !== null && _f !== void 0 ? _f : 0) > 0 && ((0,jsx_runtime.jsxs)("div", { style: { marginTop: '8px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }, children: "S\u00E9parateur entre mentions :" }), (0,jsx_runtime.jsxs)("select", { value: element.medleySeparator || 'double_newline', onChange: (e) => {
                                            onChange(element.id, 'medleySeparator', e.target.value);
                                            // Régénérer le texte avec le nouveau séparateur
                                            const selectedMentions = element.selectedMentions || [];
                                            const separatorMap = {
                                                'double_newline': '\n\n',
                                                'single_newline': '\n',
                                                'dash': ' - ',
                                                'bullet': ' • ',
                                                'pipe': ' | '
                                            };
                                            const separator = separatorMap[e.target.value] || '\n\n';
                                            const combinedText = selectedMentions
                                                .map((key) => { var _a; return (_a = predefinedMentions.find(m => m.key === key)) === null || _a === void 0 ? void 0 : _a.text; })
                                                .filter(Boolean)
                                                .join(separator);
                                            onChange(element.id, 'text', combinedText);
                                            // Ajuster la hauteur et la largeur selon le nouveau nombre de lignes
                                            const lines = combinedText.split('\n');
                                            const fontSize = typeof element.fontSize === 'string' ? parseFloat(element.fontSize) : (element.fontSize || 10);
                                            const lineHeight = fontSize * 1.3; // Harmoniser avec pdf-canvas-core.js
                                            const padding = 10; // Réduire pour cohérence
                                            const iconSpace = 20; // Espace pour l'icône
                                            const minHeight = 60; // Hauteur minimale basée sur la valeur par défaut
                                            const calculatedHeight = Math.max(minHeight, lines.length * lineHeight + iconSpace + padding);
                                            const maxHeight = 500;
                                            const newHeight = Math.min(calculatedHeight, maxHeight);
                                            // Calculer la largeur basée sur la ligne la plus longue
                                            const canvas = document.createElement('canvas');
                                            const ctx = canvas.getContext('2d');
                                            if (ctx) {
                                                ctx.font = `${element.fontWeight || 'normal'} ${fontSize}px ${element.fontFamily || 'Arial'}`;
                                                const maxLineWidth = Math.max(...lines.map((line) => ctx.measureText(line).width));
                                                const margin = 20; // Même marge qu dans pdf-canvas-core.js (width - 20)
                                                const minWidth = 200;
                                                const calculatedWidth = Math.max(minWidth, maxLineWidth + margin);
                                                const maxWidth = 800;
                                                const newWidth = Math.min(calculatedWidth, maxWidth);
                                                if (element.width !== newWidth) {
                                                    onChange(element.id, 'width', newWidth);
                                                }
                                            }
                                            if (element.height !== newHeight) {
                                                onChange(element.id, 'height', newHeight);
                                            }
                                        }, style: {
                                            width: '100%',
                                            padding: '4px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '11px'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "double_newline", children: "Double saut de ligne" }), (0,jsx_runtime.jsx)("option", { value: "single_newline", children: "Saut de ligne simple" }), (0,jsx_runtime.jsx)("option", { value: "dash", children: "Tiret (-)" }), (0,jsx_runtime.jsx)("option", { value: "bullet", children: "Point (\u2022)" }), (0,jsx_runtime.jsx)("option", { value: "pipe", children: "Barre verticale (|)" })] })] }))] })), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Texte des mentions" }), (0,jsx_runtime.jsx)("textarea", { value: element.text || '', onChange: (e) => onChange(element.id, 'text', e.target.value), placeholder: "Entrez le texte des mentions l\u00E9gales...", rows: 6, style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px',
                                    resize: 'vertical'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Afficher un s\u00E9parateur" }), (0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.showSeparator !== false, onChange: (e) => onChange(element.id, 'showSeparator', e.target.checked), style: { marginRight: '8px' } }), (0,jsx_runtime.jsx)("span", { style: { fontSize: '11px', color: '#666' }, children: "Ligne de s\u00E9paration avant les mentions" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Style du s\u00E9parateur" }), (0,jsx_runtime.jsxs)("select", { value: element.separatorStyle || 'solid', onChange: (e) => onChange(element.id, 'separatorStyle', e.target.value), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "solid", children: "Ligne continue" }), (0,jsx_runtime.jsx)("option", { value: "dashed", children: "Tirets" }), (0,jsx_runtime.jsx)("option", { value: "dotted", children: "Pointill\u00E9s" }), (0,jsx_runtime.jsx)("option", { value: "double", children: "Double ligne" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    color: '#333',
                                    marginBottom: '8px',
                                    padding: '4px 8px',
                                    backgroundColor: '#f8f9fa',
                                    borderRadius: '3px',
                                    border: '1px solid #e9ecef'
                                }, children: "Affichage du fond" }), (0,jsx_runtime.jsx)("div", { style: { paddingLeft: '8px' }, children: (0,jsx_runtime.jsx)(MentionsProperties_Toggle, { checked: element.showBackground !== false, onChange: (checked) => onChange(element.id, 'showBackground', checked), label: "Afficher le fond", description: "Affiche un fond color\u00E9 derri\u00E8re les mentions" }) })] })] })), mentionsCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }, children: "Th\u00E8me visuel" }), (0,jsx_runtime.jsx)("div", { style: {
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
                                    gap: '8px',
                                    maxHeight: '200px',
                                    overflowY: 'auto',
                                    padding: '4px',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    backgroundColor: '#fafafa'
                                }, children: mentionsThemes.map((theme) => ((0,jsx_runtime.jsxs)("div", { onClick: () => onChange(element.id, 'theme', theme.id), style: {
                                        cursor: 'pointer',
                                        border: element.theme === theme.id ? '2px solid #007bff' : '2px solid transparent',
                                        borderRadius: '6px',
                                        padding: '6px',
                                        backgroundColor: '#ffffff',
                                        transition: 'all 0.2s ease'
                                    }, title: theme.name, children: [(0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', fontWeight: 'bold', marginBottom: '4px', textAlign: 'center' }, children: theme.name }), theme.preview] }, theme.id))) })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Taille du texte" }), (0,jsx_runtime.jsxs)("select", { value: element.fontSize || '10', onChange: (e) => {
                                    var _a, _b;
                                    onChange(element.id, 'fontSize', e.target.value);
                                    // Si c'est un medley, ajuster la hauteur selon la nouvelle taille de police
                                    if (currentMentionType === 'medley' && ((_b = (_a = element.selectedMentions) === null || _a === void 0 ? void 0 : _a.length) !== null && _b !== void 0 ? _b : 0) > 0) {
                                        const selectedMentions = element.selectedMentions || [];
                                        const separatorMap = {
                                            'double_newline': '\n\n',
                                            'single_newline': '\n',
                                            'dash': ' - ',
                                            'bullet': ' • ',
                                            'pipe': ' | '
                                        };
                                        const separator = separatorMap[(element.medleySeparator || 'double_newline')] || '\n\n';
                                        const combinedText = selectedMentions
                                            .map((key) => { var _a; return (_a = predefinedMentions.find(m => m.key === key)) === null || _a === void 0 ? void 0 : _a.text; })
                                            .filter(Boolean)
                                            .join(separator);
                                        const lines = combinedText.split('\n');
                                        const fontSize = parseInt(e.target.value) || 10;
                                        const lineHeight = fontSize * 1.3; // Harmoniser avec pdf-canvas-core.js
                                        const padding = 10; // Réduire le padding pour être cohérent
                                        const iconSpace = 20; // Espace pour l'icône comme dans Canvas
                                        const minHeight = 60; // Hauteur minimale basée sur la valeur par défaut
                                        const calculatedHeight = Math.max(minHeight, lines.length * lineHeight + iconSpace + padding); // + iconSpace pour l'icône
                                        const maxHeight = 500;
                                        const newHeight = Math.min(calculatedHeight, maxHeight);
                                        // Calculer la largeur basée sur la ligne la plus longue
                                        const canvas = document.createElement('canvas');
                                        const ctx = canvas.getContext('2d');
                                        if (ctx) {
                                            ctx.font = `${element.fontWeight || 'normal'} ${fontSize}px ${element.fontFamily || 'Arial'}`;
                                            const maxLineWidth = Math.max(...lines.map((line) => ctx.measureText(line).width));
                                            const margin = 20; // Même marge qu dans pdf-canvas-core.js (width - 20)
                                            const minWidth = 200;
                                            const calculatedWidth = Math.max(minWidth, maxLineWidth + margin);
                                            const maxWidth = 800;
                                            const newWidth = Math.min(calculatedWidth, maxWidth);
                                            if (element.width !== newWidth) {
                                                onChange(element.id, 'width', newWidth);
                                            }
                                        }
                                        if (element.height !== newHeight) {
                                            onChange(element.id, 'height', newHeight);
                                        }
                                    }
                                }, style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "8", children: "Tr\u00E8s petit (8px)" }), (0,jsx_runtime.jsx)("option", { value: "10", children: "Petit (10px)" }), (0,jsx_runtime.jsx)("option", { value: "11", children: "Normal (11px)" }), (0,jsx_runtime.jsx)("option", { value: "12", children: "Moyen (12px)" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Alignement du texte" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Style du texte" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsxs)("label", { style: { fontSize: '11px', display: 'flex', alignItems: 'center' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.fontWeight === 'bold', onChange: (e) => onChange(element.id, 'fontWeight', e.target.checked ? 'bold' : 'normal'), style: { marginRight: '4px' } }), "Gras"] }), (0,jsx_runtime.jsxs)("label", { style: { fontSize: '11px', color: '#666', display: 'flex', alignItems: 'center' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.fontStyle === 'italic', onChange: (e) => onChange(element.id, 'fontStyle', e.target.checked ? 'italic' : 'normal'), style: { marginRight: '4px' } }), "Italique"] })] })] }), element.showBackground !== false && ((0,jsx_runtime.jsx)("div", { style: { marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }, children: (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor || '#e5e7eb', onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                        width: '100%',
                                        height: '40px',
                                        border: '1px solid #d1d5db',
                                        borderRadius: '6px',
                                        cursor: 'pointer'
                                    } })] }) }))] })), mentionsCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Largeur ", currentMentionType === 'medley' ? '(manuel = clipping activé)' : ''] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width || 500, onChange: (e) => onChange(element.id, 'width', parseInt(e.target.value) || 500), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } }), currentMentionType === 'medley' && ((0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '4px' }, children: "Redimensionner manuellement active le clipping du texte" }))] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Hauteur ", currentMentionType === 'medley' ? '(manuel = clipping activé)' : ''] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height || 60, onChange: (e) => onChange(element.id, 'height', parseInt(e.target.value) || 60), style: {
                                    width: '100%',
                                    padding: '6px',
                                    border: '1px solid #ccc',
                                    borderRadius: '4px',
                                    fontSize: '12px'
                                } }), currentMentionType === 'medley' && ((0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#666', marginTop: '4px' }, children: "Redimensionner manuellement active le clipping du texte" }))] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/DocumentTypeProperties.tsx

// Composant Toggle personnalisé
const DocumentTypeProperties_Toggle = ({ checked, onChange, label, description }) => ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '6px'
            }, children: [(0,jsx_runtime.jsx)("label", { style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        color: '#333',
                        flex: 1
                    }, children: label }), (0,jsx_runtime.jsx)("div", { onClick: () => onChange(!checked), style: {
                        position: 'relative',
                        width: '44px',
                        height: '24px',
                        backgroundColor: checked ? '#007bff' : '#ccc',
                        borderRadius: '12px',
                        cursor: 'pointer',
                        transition: 'background-color 0.2s ease',
                        border: 'none'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            position: 'absolute',
                            top: '2px',
                            left: checked ? '22px' : '2px',
                            width: '20px',
                            height: '20px',
                            backgroundColor: 'white',
                            borderRadius: '50%',
                            transition: 'left 0.2s ease',
                            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
                        } }) })] }), (0,jsx_runtime.jsx)("div", { style: {
                fontSize: '11px',
                color: '#666',
                lineHeight: '1.4'
            }, children: description })] }));
function DocumentTypeProperties({ element, onChange, activeTab, setActiveTab }) {
    const documentCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setDocumentCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    const documentTypes = [
        { value: 'FACTURE', label: 'Facture' },
        { value: 'DEVIS', label: 'Devis' },
        { value: 'BON_COMMANDE', label: 'Bon de Commande' },
        { value: 'AVOIR', label: 'Avoir' },
        { value: 'RELEVE', label: 'Relevé' },
        { value: 'CONTRAT', label: 'Contrat' }
    ];
    return ((0,jsx_runtime.jsxs)("div", { style: { padding: '16px', backgroundColor: '#ffffff', borderRadius: '8px', marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("div", { style: { display: 'flex', marginBottom: '16px', borderBottom: '1px solid #e5e7eb' }, children: [
                    { key: 'fonctionnalites', label: 'Fonctionnalités' },
                    { key: 'personnalisation', label: 'Personnalisation' },
                    { key: 'positionnement', label: 'Positionnement' }
                ].map(tab => ((0,jsx_runtime.jsx)("button", { onClick: () => setDocumentCurrentTab(tab.key), style: {
                        padding: '8px 16px',
                        border: 'none',
                        backgroundColor: documentCurrentTab === tab.key ? '#007acc' : 'transparent',
                        color: documentCurrentTab === tab.key ? '#ffffff' : '#6b7280',
                        borderRadius: '4px 4px 0 0',
                        cursor: 'pointer',
                        fontSize: '12px',
                        fontWeight: '500'
                    }, children: tab.label }, tab.key))) }), documentCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }, children: "Type de Document" }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '16px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Type" }), (0,jsx_runtime.jsx)("select", { value: element.documentType || 'FACTURE', onChange: (e) => onChange(element.id, 'documentType', e.target.value), style: {
                                    width: '100%',
                                    padding: '8px 12px',
                                    border: '1px solid #d1d5db',
                                    borderRadius: '6px',
                                    fontSize: '14px',
                                    backgroundColor: '#ffffff'
                                }, children: documentTypes.map(type => ((0,jsx_runtime.jsx)("option", { value: type.value, children: type.label }, type.value))) })] }), (0,jsx_runtime.jsxs)("div", { style: { marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }, children: "Affichage du fond" }), (0,jsx_runtime.jsx)(DocumentTypeProperties_Toggle, { checked: element.showBackground !== false, onChange: (checked) => onChange(element.id, 'showBackground', checked), label: "Afficher le fond", description: "Affiche un fond color\u00E9 derri\u00E8re le texte" })] })] })), documentCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }, children: "Apparence du Texte" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'grid', gap: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.fontSize || 18, onChange: (e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 18), min: "8", max: "72", style: {
                                            width: '100%',
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            fontSize: '14px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("select", { value: element.fontWeight || 'bold', onChange: (e) => onChange(element.id, 'fontWeight', e.target.value), style: {
                                            width: '100%',
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            fontSize: '14px',
                                            backgroundColor: '#ffffff'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "normal", children: "Normal" }), (0,jsx_runtime.jsx)("option", { value: "bold", children: "Gras" }), (0,jsx_runtime.jsx)("option", { value: "bolder", children: "Tr\u00E8s gras" }), (0,jsx_runtime.jsx)("option", { value: "lighter", children: "Fin" })] })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Alignement" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                            width: '100%',
                                            padding: '8px 12px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            fontSize: '14px',
                                            backgroundColor: '#ffffff'
                                        }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Couleur du texte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || '#000000', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                            width: '100%',
                                            height: '40px',
                                            border: '1px solid #d1d5db',
                                            borderRadius: '6px',
                                            cursor: 'pointer'
                                        } })] })] }), element.showBackground !== false && ((0,jsx_runtime.jsxs)("div", { style: { marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor || '#e5e7eb', onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                    width: '100%',
                                    height: '40px',
                                    border: '1px solid #d1d5db',
                                    borderRadius: '6px',
                                    cursor: 'pointer'
                                } })] }))] })), documentCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }, children: "Position et Dimensions" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'grid', gap: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                                    width: '100%',
                                                    padding: '8px 12px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '6px',
                                                    fontSize: '14px'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                                    width: '100%',
                                                    padding: '8px 12px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '6px',
                                                    fontSize: '14px'
                                                } })] })] }), (0,jsx_runtime.jsxs)("div", { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 0), style: {
                                                    width: '100%',
                                                    padding: '8px 12px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '6px',
                                                    fontSize: '14px'
                                                } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height, onChange: (e) => onChange(element.id, 'height', parseFloat(e.target.value) || 0), style: {
                                                    width: '100%',
                                                    padding: '8px 12px',
                                                    border: '1px solid #d1d5db',
                                                    borderRadius: '6px',
                                                    fontSize: '14px'
                                                } })] })] })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/TextProperties.tsx

function TextProperties({ element, onChange, activeTab, setActiveTab }) {
    const textCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setTextCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setTextCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: textCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: textCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setTextCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: textCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: textCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setTextCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: textCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: textCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), textCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Contenu du texte" }), (0,jsx_runtime.jsx)("textarea", { value: element.text || '', onChange: (e) => onChange(element.id, 'text', e.target.value), style: {
                                    width: '100%',
                                    minHeight: '60px',
                                    padding: '8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px',
                                    fontFamily: 'monospace',
                                    resize: 'vertical'
                                }, placeholder: "Entrez votre texte ici..." })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Alignement horizontal" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || element.align || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" }), (0,jsx_runtime.jsx)("option", { value: "justify", children: "Justifi\u00E9" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Alignement vertical" }), (0,jsx_runtime.jsxs)("select", { value: element.verticalAlign || 'top', onChange: (e) => onChange(element.id, 'verticalAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "top", children: "Haut" }), (0,jsx_runtime.jsx)("option", { value: "middle", children: "Milieu" }), (0,jsx_runtime.jsx)("option", { value: "bottom", children: "Bas" })] })] })] })), textCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Police" }), (0,jsx_runtime.jsxs)("select", { value: element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'fontFamily', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Courier New", children: "Courier New" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" }), (0,jsx_runtime.jsx)("option", { value: "Tahoma", children: "Tahoma" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Taille de police ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.fontSize || 16, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "6", max: "72", value: element.fontSize || 16, onChange: (e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 16), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Style de police" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '4px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => onChange(element.id, 'fontWeight', element.fontWeight === 'bold' ? 'normal' : 'bold'), style: {
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            backgroundColor: element.fontWeight === 'bold' ? '#007bff' : '#f8f9fa',
                                            color: element.fontWeight === 'bold' ? '#fff' : '#333',
                                            cursor: 'pointer',
                                            fontSize: '11px',
                                            fontWeight: 'bold'
                                        }, children: "B" }), (0,jsx_runtime.jsx)("button", { onClick: () => onChange(element.id, 'fontStyle', element.fontStyle === 'italic' ? 'normal' : 'italic'), style: {
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            backgroundColor: element.fontStyle === 'italic' ? '#007bff' : '#f8f9fa',
                                            color: element.fontStyle === 'italic' ? '#fff' : '#333',
                                            cursor: 'pointer',
                                            fontSize: '11px',
                                            fontStyle: 'italic'
                                        }, children: "I" }), (0,jsx_runtime.jsx)("button", { onClick: () => onChange(element.id, 'textDecoration', element.textDecoration === 'underline' ? 'none' : 'underline'), style: {
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            backgroundColor: element.textDecoration === 'underline' ? '#007bff' : '#f8f9fa',
                                            color: element.textDecoration === 'underline' ? '#fff' : '#333',
                                            cursor: 'pointer',
                                            fontSize: '11px',
                                            textDecoration: 'underline'
                                        }, children: "U" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur du texte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.textColor || element.color || '#000000', onChange: (e) => onChange(element.id, 'textColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    cursor: 'pointer'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.backgroundColor === 'transparent' ? '#ffffff' : (element.backgroundColor || '#ffffff'), onChange: (e) => onChange(element.id, 'backgroundColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    cursor: 'pointer'
                                } })] })] })), textCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position X ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.x || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position Y ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.y || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Largeur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.width || 100, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.width || 100, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Hauteur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.height || 50, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.height || 50, onChange: (e) => onChange(element.id, 'height', parseFloat(e.target.value) || 50), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Rotation ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.rotation || 0, "\u00B0)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "-180", max: "180", value: element.rotation || 0, onChange: (e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Opacit\u00E9 ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", Math.round((element.opacity || 1) * 100), "%)"] })] }), (0,jsx_runtime.jsx)("input", { type: "range", min: "0", max: "1", step: "0.1", value: element.opacity || 1, onChange: (e) => onChange(element.id, 'opacity', parseFloat(e.target.value)), style: { width: '100%' } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/ShapeProperties.tsx

function ShapeProperties({ element, onChange, activeTab, setActiveTab }) {
    const shapeCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setShapeCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setShapeCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: shapeCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: shapeCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setShapeCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: shapeCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: shapeCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setShapeCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: shapeCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: shapeCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), shapeCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [element.type === 'circle' && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Forme" }), (0,jsx_runtime.jsx)("div", { style: { padding: '8px', backgroundColor: '#f8f9fa', borderRadius: '4px', textAlign: 'center' }, children: "Cercle" })] })), element.type === 'rectangle' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Forme" }), (0,jsx_runtime.jsx)("div", { style: { padding: '8px', backgroundColor: '#f8f9fa', borderRadius: '4px', textAlign: 'center' }, children: "Rectangle" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Rayon des coins ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.borderRadius || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", max: "50", value: element.borderRadius || 0, onChange: (e) => onChange(element.id, 'borderRadius', parseInt(e.target.value) || 0), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] })] }))] })), shapeCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur de remplissage" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.fillColor === 'transparent' ? '#ffffff' : (element.fillColor || '#007bff'), onChange: (e) => onChange(element.id, 'fillColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    cursor: 'pointer'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur de bordure" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.strokeColor === 'transparent' ? '#000000' : (element.strokeColor || '#000000'), onChange: (e) => onChange(element.id, 'strokeColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    cursor: 'pointer'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["\u00C9paisseur de bordure ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.strokeWidth || 1, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", max: "20", value: element.strokeWidth || 1, onChange: (e) => onChange(element.id, 'strokeWidth', parseInt(e.target.value) || 1), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] })] })), shapeCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position X ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.x || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position Y ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.y || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Largeur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.width || 100, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.width || 100, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Hauteur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.height || 100, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.height || 100, onChange: (e) => onChange(element.id, 'height', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Rotation ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.rotation || 0, "\u00B0)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "-180", max: "180", value: element.rotation || 0, onChange: (e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Opacit\u00E9 ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", Math.round((element.opacity || 1) * 100), "%)"] })] }), (0,jsx_runtime.jsx)("input", { type: "range", min: "0", max: "1", step: "0.1", value: element.opacity || 1, onChange: (e) => onChange(element.id, 'opacity', parseFloat(e.target.value)), style: { width: '100%' } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/ImageProperties.tsx

function ImageProperties({ element, onChange, activeTab, setActiveTab }) {
    const imageCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setImageCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    const openMediaLibrary = () => {
        var _a;
        if ((_a = window.wp) === null || _a === void 0 ? void 0 : _a.media) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            const media = window.wp.media({
                title: 'Sélectionner une image',
                button: {
                    text: 'Utiliser cette image'
                },
                multiple: false
            });
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            media.on('select', () => {
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                const attachment = media.state().get('selection').first().toJSON();
                onChange(element.id, 'src', attachment.url);
            });
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            media.open();
        }
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setImageCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: imageCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: imageCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setImageCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: imageCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: imageCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setImageCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: imageCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: imageCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), imageCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Source de l'image" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '4px' }, children: [(0,jsx_runtime.jsx)("input", { type: "text", value: element.src || '', onChange: (e) => onChange(element.id, 'src', e.target.value), placeholder: "URL de l'image", style: {
                                            flex: 1,
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } }), (0,jsx_runtime.jsx)("button", { onClick: openMediaLibrary, style: {
                                            padding: '4px 8px',
                                            border: '1px solid #007bff',
                                            borderRadius: '3px',
                                            backgroundColor: '#007bff',
                                            color: '#fff',
                                            cursor: 'pointer',
                                            fontSize: '11px'
                                        }, title: "Ouvrir la m\u00E9diath\u00E8que WordPress", children: "\uD83D\uDCC1" })] })] }), element.src && ((0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Aper\u00E7u" }), (0,jsx_runtime.jsx)("div", { style: {
                                    padding: '8px',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    backgroundColor: '#f8f9fa',
                                    textAlign: 'center'
                                }, children: (0,jsx_runtime.jsx)("img", { src: element.src, alt: "Aper\u00E7u", style: {
                                        maxWidth: '100%',
                                        maxHeight: '100px',
                                        borderRadius: '4px',
                                        boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                                    } }) })] }))] })), imageCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsx)(jsx_runtime.Fragment, { children: (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Ajustement de l'image" }), (0,jsx_runtime.jsxs)("select", { value: element.objectFit || 'contain', onChange: (e) => onChange(element.id, 'objectFit', e.target.value), style: {
                                width: '100%',
                                padding: '4px 8px',
                                border: '1px solid #ccc',
                                borderRadius: '3px',
                                fontSize: '12px'
                            }, children: [(0,jsx_runtime.jsx)("option", { value: "contain", children: "Contenir (respecter les proportions)" }), (0,jsx_runtime.jsx)("option", { value: "cover", children: "Couvrir (remplir compl\u00E8tement)" }), (0,jsx_runtime.jsx)("option", { value: "fill", children: "Remplir (\u00E9tirer si n\u00E9cessaire)" }), (0,jsx_runtime.jsx)("option", { value: "none", children: "Aucun (taille originale)" }), (0,jsx_runtime.jsx)("option", { value: "scale-down", children: "R\u00E9duire (taille originale ou contenir)" })] })] }) })), imageCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position X ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.x || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position Y ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.y || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Largeur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.width || 100, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.width || 100, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Hauteur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.height || 100, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.height || 100, onChange: (e) => onChange(element.id, 'height', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Rotation ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.rotation || 0, "\u00B0)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "-180", max: "180", value: element.rotation || 0, onChange: (e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Opacit\u00E9 ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", Math.round((element.opacity || 1) * 100), "%)"] })] }), (0,jsx_runtime.jsx)("input", { type: "range", min: "0", max: "1", step: "0.1", value: element.opacity || 1, onChange: (e) => onChange(element.id, 'opacity', parseFloat(e.target.value)), style: { width: '100%' } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/LineProperties.tsx

function LineProperties({ element, onChange, activeTab, setActiveTab }) {
    const lineCurrentTab = activeTab[element.id] || 'fonctionnalites';
    const setLineCurrentTab = (tab) => {
        setActiveTab({ ...activeTab, [element.id]: tab });
    };
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setLineCurrentTab('fonctionnalites'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: lineCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                            color: lineCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Fonctionnalit\u00E9s", children: "Fonctionnalit\u00E9s" }), (0,jsx_runtime.jsx)("button", { onClick: () => setLineCurrentTab('personnalisation'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: lineCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                            color: lineCurrentTab === 'personnalisation' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Personnalisation", children: "Personnalisation" }), (0,jsx_runtime.jsx)("button", { onClick: () => setLineCurrentTab('positionnement'), style: {
                            flex: '1 1 30%',
                            padding: '8px 6px',
                            backgroundColor: lineCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                            color: lineCurrentTab === 'positionnement' ? '#fff' : '#333',
                            border: 'none',
                            cursor: 'pointer',
                            fontSize: '11px',
                            fontWeight: 'bold',
                            borderRadius: '3px 3px 0 0',
                            minWidth: '0',
                            whiteSpace: 'nowrap',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis'
                        }, title: "Positionnement", children: "Positionnement" })] }), lineCurrentTab === 'fonctionnalites' && ((0,jsx_runtime.jsx)(jsx_runtime.Fragment, { children: (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Type d'\u00E9l\u00E9ment" }), (0,jsx_runtime.jsx)("div", { style: { padding: '8px', backgroundColor: '#f8f9fa', borderRadius: '4px', textAlign: 'center' }, children: "Ligne" })] }) })), lineCurrentTab === 'personnalisation' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Couleur de la ligne" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.strokeColor || '#000000', onChange: (e) => onChange(element.id, 'strokeColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    cursor: 'pointer'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["\u00C9paisseur de la ligne ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.strokeWidth || 2, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", max: "20", value: element.strokeWidth || 2, onChange: (e) => onChange(element.id, 'strokeWidth', parseInt(e.target.value) || 2), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Opacit\u00E9 ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", Math.round((element.opacity || 1) * 100), "%)"] })] }), (0,jsx_runtime.jsx)("input", { type: "range", min: "0", max: "1", step: "0.1", value: element.opacity || 1, onChange: (e) => onChange(element.id, 'opacity', parseFloat(e.target.value)), style: { width: '100%' } })] })] })), lineCurrentTab === 'positionnement' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position X ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.x || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Position Y ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.y || 0, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Longueur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.width || 100, "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", value: element.width || 100, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: ["Rotation ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", element.rotation || 0, "\u00B0)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "-180", max: "180", value: element.rotation || 0, onChange: (e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/ElementProperties.tsx

function ElementProperties({ element, onChange }) {
    return ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Largeur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.width || 100) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", step: "0.1", value: element.width || 100, onChange: (e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100), style: {
                            width: '100%',
                            padding: '4px 8px',
                            border: '1px solid #ccc',
                            borderRadius: '3px',
                            fontSize: '12px'
                        }, placeholder: "Valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Entrer la largeur en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Hauteur ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.height || 50) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "1", step: "0.1", value: element.height || 50, onChange: (e) => onChange(element.id, 'height', parseFloat(e.target.value) || 50), style: {
                            width: '100%',
                            padding: '4px 8px',
                            border: '1px solid #ccc',
                            borderRadius: '3px',
                            fontSize: '12px'
                        }, placeholder: "Valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Entrer la hauteur en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Position X ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.x || 0) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", step: "0.1", value: element.x || 0, onChange: (e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                            width: '100%',
                            padding: '4px 8px',
                            border: '1px solid #ccc',
                            borderRadius: '3px',
                            fontSize: '12px'
                        }, placeholder: "Valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Entrer la position en pixels" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: ["Position Y ", (0,jsx_runtime.jsxs)("span", { style: { color: '#666', fontSize: '10px' }, children: ["(", ((element.y || 0) * 1).toFixed(1), "px)"] })] }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", step: "0.1", value: element.y || 0, onChange: (e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                            width: '100%',
                            padding: '4px 8px',
                            border: '1px solid #ccc',
                            borderRadius: '3px',
                            fontSize: '12px'
                        }, placeholder: "Valeur en pixels" }), (0,jsx_runtime.jsx)("small", { style: { color: '#999', display: 'block', marginTop: '2px' }, children: "Entrer la position en pixels" })] }), element.type === 'rectangle' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.fillColor || '#ffffff', onChange: (e) => onChange(element.id, 'fillColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de bordure" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.strokeColor || '#000000', onChange: (e) => onChange(element.id, 'strokeColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de bordure" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", max: "20", value: element.strokeWidth || 1, onChange: (e) => onChange(element.id, 'strokeWidth', parseInt(e.target.value) || 1), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Rayon des coins" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", max: "50", value: element.borderRadius || 0, onChange: (e) => onChange(element.id, 'borderRadius', parseInt(e.target.value) || 0), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] })] })), element.type === 'circle' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de fond" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.fillColor || '#ffffff', onChange: (e) => onChange(element.id, 'fillColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur de bordure" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.strokeColor || '#000000', onChange: (e) => onChange(element.id, 'strokeColor', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "\u00C9paisseur de bordure" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "0", max: "20", value: element.strokeWidth || 1, onChange: (e) => onChange(element.id, 'strokeWidth', parseInt(e.target.value) || 1), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] })] })), element.type === 'text' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Texte" }), (0,jsx_runtime.jsx)("textarea", { value: element.text || '', onChange: (e) => onChange(element.id, 'text', e.target.value), rows: 3, style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px',
                                    resize: 'vertical'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Taille de police" }), (0,jsx_runtime.jsx)("input", { type: "number", min: "8", max: "72", value: element.fontSize || 12, onChange: (e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 12), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Couleur du texte" }), (0,jsx_runtime.jsx)("input", { type: "color", value: element.color || '#000000', onChange: (e) => onChange(element.id, 'color', e.target.value), style: {
                                    width: '100%',
                                    height: '32px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Alignement" }), (0,jsx_runtime.jsxs)("select", { value: element.textAlign || 'left', onChange: (e) => onChange(element.id, 'textAlign', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "left", children: "Gauche" }), (0,jsx_runtime.jsx)("option", { value: "center", children: "Centre" }), (0,jsx_runtime.jsx)("option", { value: "right", children: "Droite" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Police" }), (0,jsx_runtime.jsxs)("select", { value: element.fontFamily || 'Arial', onChange: (e) => onChange(element.id, 'fontFamily', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "Arial", children: "Arial" }), (0,jsx_runtime.jsx)("option", { value: "Helvetica", children: "Helvetica" }), (0,jsx_runtime.jsx)("option", { value: "Times New Roman", children: "Times New Roman" }), (0,jsx_runtime.jsx)("option", { value: "Courier New", children: "Courier New" }), (0,jsx_runtime.jsx)("option", { value: "Georgia", children: "Georgia" }), (0,jsx_runtime.jsx)("option", { value: "Verdana", children: "Verdana" })] })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }, children: "Style du texte" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' }, children: [(0,jsx_runtime.jsxs)("label", { style: { display: 'flex', alignItems: 'center', fontSize: '11px' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.bold || false, onChange: (e) => onChange(element.id, 'bold', e.target.checked), style: { marginRight: '4px' } }), "Gras"] }), (0,jsx_runtime.jsxs)("label", { style: { display: 'flex', alignItems: 'center', fontSize: '11px' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.italic || false, onChange: (e) => onChange(element.id, 'italic', e.target.checked), style: { marginRight: '4px' } }), "Italique"] }), (0,jsx_runtime.jsxs)("label", { style: { display: 'flex', alignItems: 'center', fontSize: '11px' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: element.underline || false, onChange: (e) => onChange(element.id, 'underline', e.target.checked), style: { marginRight: '4px' } }), "Soulign\u00E9"] })] })] })] })), element.type === 'image' && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("hr", { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "URL de l'image" }), (0,jsx_runtime.jsx)("input", { type: "text", value: element.src || '', onChange: (e) => onChange(element.id, 'src', e.target.value), placeholder: "https://example.com/image.jpg", style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                } })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '12px' }, children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Ajustement" }), (0,jsx_runtime.jsxs)("select", { value: element.objectFit || 'contain', onChange: (e) => onChange(element.id, 'objectFit', e.target.value), style: {
                                    width: '100%',
                                    padding: '4px 8px',
                                    border: '1px solid #ccc',
                                    borderRadius: '3px',
                                    fontSize: '12px'
                                }, children: [(0,jsx_runtime.jsx)("option", { value: "contain", children: "Contenir" }), (0,jsx_runtime.jsx)("option", { value: "cover", children: "Couvrir" }), (0,jsx_runtime.jsx)("option", { value: "fill", children: "Remplir" }), (0,jsx_runtime.jsx)("option", { value: "none", children: "Aucun" }), (0,jsx_runtime.jsx)("option", { value: "scale-down", children: "R\u00E9duire" })] })] })] }))] }));
}

;// ./assets/js/pdf-builder-react/components/properties/PropertiesPanel.tsx


















const PropertiesPanel = (0,react.memo)(function PropertiesPanel({ className }) {
    const { state, updateElement, removeElement } = useBuilder();
    const [activeTab, setActiveTab] = (0,react.useState)({});
    const isMobile = useResponsive_useIsMobile();
    const isTablet = useResponsive_useIsTablet();
    // Optimisation: mémoriser les éléments sélectionnés
    const selectedElements = (0,react.useMemo)(() => state.elements.filter(el => state.selection.selectedElements.includes(el.id)), [state.elements, state.selection.selectedElements]);
    // Optimisation: mémoriser les handlers
    const handlePropertyChange = (0,react.useCallback)((elementId, property, value) => {
        updateElement(elementId, { [property]: value });
    }, [updateElement]);
    const handleDeleteSelected = (0,react.useCallback)(() => {
        state.selection.selectedElements.forEach(id => {
            removeElement(id);
        });
    }, [state.selection.selectedElements, removeElement]);
    if (selectedElements.length === 0) {
        return ((0,jsx_runtime.jsx)(ResponsiveContainer, { className: `pdf-builder-properties ${className || ''}`, mobileClass: "properties-panel-mobile", tabletClass: "properties-panel-tablet", desktopClass: "properties-panel-desktop", children: (0,jsx_runtime.jsxs)("div", { style: {
                    padding: isMobile ? '8px' : '12px',
                    backgroundColor: '#f9f9f9',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    minHeight: isMobile ? '150px' : '200px',
                    maxHeight: isMobile ? 'calc(50vh - 16px)' : 'calc(100vh - 32px)',
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    textAlign: 'center'
                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                            fontSize: isMobile ? '24px' : '32px',
                            marginBottom: '8px',
                            opacity: 0.5
                        }, children: "\uD83C\uDFAF" }), (0,jsx_runtime.jsx)("div", { style: {
                            fontSize: isMobile ? '12px' : '14px',
                            color: '#666',
                            fontWeight: '500'
                        }, children: isMobile ? 'Sélectionnez un élément' : 'Sélectionnez un élément pour modifier ses propriétés' }), (0,jsx_runtime.jsx)("div", { style: {
                            fontSize: isMobile ? '10px' : '12px',
                            color: '#999',
                            marginTop: '4px',
                            display: isMobile ? 'none' : 'block'
                        }, children: "Cliquez sur un \u00E9l\u00E9ment du canvas pour commencer" })] }) }));
    }
    return ((0,jsx_runtime.jsxs)("div", { className: `pdf-builder-properties ${className || ''}`, style: {
            padding: '12px',
            backgroundColor: '#f9f9f9',
            border: '1px solid #ddd',
            borderRadius: '4px',
            maxHeight: 'calc(100vh - 32px)',
            overflowY: 'auto'
        }, children: [(0,jsx_runtime.jsxs)("div", { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }, children: [(0,jsx_runtime.jsxs)("h4", { style: { margin: '0', fontSize: '14px', fontWeight: 'bold' }, children: ["Propri\u00E9t\u00E9s (", selectedElements.length, ")"] }), (0,jsx_runtime.jsx)("div", { style: { display: 'flex', gap: '4px' }, children: (0,jsx_runtime.jsx)("button", { onClick: handleDeleteSelected, style: {
                                padding: '4px 8px',
                                border: '1px solid #dc3545',
                                borderRadius: '4px',
                                backgroundColor: '#dc3545',
                                color: '#ffffff',
                                cursor: 'pointer',
                                fontSize: '12px'
                            }, children: "\uD83D\uDDD1\uFE0F Supprimer" }) })] }), selectedElements.map(element => ((0,jsx_runtime.jsxs)("div", { style: {
                    marginBottom: '16px',
                    padding: '12px',
                    backgroundColor: '#ffffff',
                    border: '1px solid #e0e0e0',
                    borderRadius: '4px',
                    maxHeight: 'calc(100vh - 120px)',
                    overflowY: 'auto'
                }, children: [(0,jsx_runtime.jsxs)("h5", { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' }, children: [element.type.charAt(0).toUpperCase() + element.type.slice(1), " - ", element.id.slice(0, 8)] }), element.type !== 'product_table' && element.type !== 'customer_info' && element.type !== 'company_info' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'document_type' && element.type !== 'dynamic-text' && element.type !== 'mentions' && element.type !== 'text' && element.type !== 'rectangle' && element.type !== 'circle' && element.type !== 'image' && element.type !== 'line' && ((0,jsx_runtime.jsxs)("div", { style: { display: 'grid', gap: '8px' }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Position X" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.x, onChange: (e) => handlePropertyChange(element.id, 'x', parseFloat(e.target.value) || 0), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Position Y" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.y, onChange: (e) => handlePropertyChange(element.id, 'y', parseFloat(e.target.value) || 0), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Largeur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.width, onChange: (e) => handlePropertyChange(element.id, 'width', parseFloat(e.target.value) || 0), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Hauteur" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.height, onChange: (e) => handlePropertyChange(element.id, 'height', parseFloat(e.target.value) || 0), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Rotation (\u00B0)" }), (0,jsx_runtime.jsx)("input", { type: "number", value: element.rotation || 0, onChange: (e) => handlePropertyChange(element.id, 'rotation', parseFloat(e.target.value) || 0), style: {
                                            width: '100%',
                                            padding: '4px 8px',
                                            border: '1px solid #ccc',
                                            borderRadius: '3px',
                                            fontSize: '12px'
                                        } })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }, children: "Opacit\u00E9" }), (0,jsx_runtime.jsx)("input", { type: "range", min: "0", max: "1", step: "0.1", value: element.opacity || 1, onChange: (e) => handlePropertyChange(element.id, 'opacity', parseFloat(e.target.value)), style: { width: '100%' } }), (0,jsx_runtime.jsxs)("span", { style: { fontSize: '11px', color: '#666' }, children: [Math.round((element.opacity || 1) * 100), "%"] })] })] })), element.type === 'product_table' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(ProductTableProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'customer_info' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(CustomerInfoProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'company_info' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(CompanyInfoProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'company_logo' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(CompanyLogoProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'order_number' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(OrderNumberProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'document_type' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(DocumentTypeProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'dynamic-text' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(DynamicTextProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'mentions' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(MentionsProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'text' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(TextProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), (element.type === 'rectangle' || element.type === 'circle') && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(ShapeProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'image' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(ImageProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), element.type === 'line' && (
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    (0,jsx_runtime.jsx)(LineProperties, { element: element, onChange: handlePropertyChange, activeTab: activeTab, setActiveTab: setActiveTab })), (element.type !== 'product_table' && element.type !== 'customer_info' && element.type !== 'company_info' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'document_type' && element.type !== 'dynamic-text' && element.type !== 'mentions' && element.type !== 'text' && element.type !== 'rectangle' && element.type !== 'circle' && element.type !== 'image' && element.type !== 'line') && ((0,jsx_runtime.jsx)(ElementProperties, { element: element, onChange: handlePropertyChange }))] }, element.id)))] }));
});

;// ./assets/js/pdf-builder-react/hooks/usePreview.ts

/**
 * Hook React usePreview pour gérer l'aperçu PDF
 * Implémente les exigences de l'étape 1.5 du roadmap
 */
function usePreview() {
    const [isModalOpen, setIsModalOpen] = (0,react.useState)(false);
    const [isGenerating, setIsGenerating] = (0,react.useState)(false);
    const [previewUrl, setPreviewUrl] = (0,react.useState)(null);
    const [error, setError] = (0,react.useState)(null);
    const [format, setFormat] = (0,react.useState)('png');
    const openModal = (0,react.useCallback)(() => {
        setIsModalOpen(true);
        setError(null);
        setPreviewUrl(null);
    }, []);
    const closeModal = (0,react.useCallback)(() => {
        setIsModalOpen(false);
        setIsGenerating(false);
        setPreviewUrl(null);
        setError(null);
    }, []);
    const clearPreview = (0,react.useCallback)(() => {
        setPreviewUrl(null);
        setError(null);
    }, []);
    const generatePreview = (0,react.useCallback)(async (templateData, options = {}) => {
        const finalFormat = options.format || format;
        const quality = options.quality || 150;
        setIsGenerating(true);
        setError(null);
        setPreviewUrl(null);
        try {
            // Vérifier s'il y a du contenu dans le template
            const hasContent = templateData.elements && templateData.elements.length > 0;
            if (!hasContent) {
                throw new Error('Aucun contenu dans le template. Ajoutez des éléments avant de générer un aperçu.');
            }
            // Vérifier la disponibilité de l'API Preview
            if (typeof window.pdfPreviewAPI === 'undefined') {
                throw new Error('API Preview non disponible. Vérifiez que les scripts sont chargés.');
            }
            // Générer l'aperçu
            const result = await window.pdfPreviewAPI.generateEditorPreview(templateData, { format: finalFormat, quality });
            if (result && typeof result === 'object' && 'success' in result && result.success && 'image_url' in result && typeof result.image_url === 'string') {
                if (finalFormat === 'pdf') {
                    // Pour PDF, ouvrir dans un nouvel onglet
                    window.open(result.image_url, '_blank');
                    setPreviewUrl(null); // Ne pas afficher dans la modale
                }
                else {
                    // Pour PNG/JPG, afficher dans la modale
                    setPreviewUrl(result.image_url);
                }
            }
            else {
                const errorMsg = (result && typeof result === 'object' && 'error' in result && typeof result.error === 'string') ? result.error : 'Erreur lors de la génération de l\'aperçu';
                throw new Error(errorMsg);
            }
        }
        catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Erreur inconnue lors de la génération';
            setError(errorMessage);
        }
        finally {
            setIsGenerating(false);
        }
    }, [format]);
    return {
        // État de la modale
        isModalOpen,
        openModal,
        closeModal,
        // État de génération
        isGenerating,
        previewUrl,
        error,
        // Options
        format,
        setFormat,
        // Actions
        generatePreview,
        clearPreview
    };
}
/* harmony default export */ const hooks_usePreview = ((/* unused pure expression or super */ null && (usePreview)));

;// ./assets/js/pdf-builder-react/components/header/Header.tsx






const Header = (0,react.memo)(function Header({ templateName, templateDescription, canvasWidth, canvasHeight, showGuides, snapToGrid, isNewTemplate, isModified, isSaving, isLoading, isEditingExistingTemplate, onSave, onPreview: _onPreview, onNewTemplate, onUpdateTemplateSettings }) {
    // Use deferred values for frequently changing props to prevent cascading re-renders
    const deferredIsModified = (0,react.useDeferredValue)(isModified);
    const deferredIsSaving = (0,react.useDeferredValue)(isSaving);
    const deferredIsLoading = (0,react.useDeferredValue)(isLoading);
    const deferredIsEditingExistingTemplate = (0,react.useDeferredValue)(isEditingExistingTemplate);
    // Debug logging
    (0,react.useEffect)(() => {
    }, []);
    const { state } = useBuilder();
    const canvasSettings = CanvasSettingsContext_useCanvasSettings();
    const [hoveredButton, setHoveredButton] = (0,react.useState)(null);
    const [showSettingsModal, setShowSettingsModal] = (0,react.useState)(false);
    const [showJsonModal, setShowJsonModal] = (0,react.useState)(false);
    const [copySuccess, setCopySuccess] = (0,react.useState)(false);
    const [isHeaderFixed, setIsHeaderFixed] = (0,react.useState)(false);
    const [editedTemplateName, setEditedTemplateName] = (0,react.useState)(templateName);
    const [editedTemplateDescription, setEditedTemplateDescription] = (0,react.useState)(templateDescription);
    const [editedCanvasWidth, setEditedCanvasWidth] = (0,react.useState)(canvasWidth);
    const [editedCanvasHeight, setEditedCanvasHeight] = (0,react.useState)(canvasHeight);
    const [editedShowGuides, setEditedShowGuides] = (0,react.useState)(showGuides);
    const [editedSnapToGrid, setEditedSnapToGrid] = (0,react.useState)(snapToGrid);
    const [showPredefinedTemplates, setShowPredefinedTemplates] = (0,react.useState)(false);
    // Utiliser le hook usePreview pour la gestion de l'aperçu
    const { isModalOpen: showPreviewModal, openModal: openPreviewModal, closeModal: closePreviewModal, isGenerating: isGeneratingPreview, previewUrl: previewImageUrl, error: previewError, format: previewFormat, setFormat: setPreviewFormat, generatePreview, clearPreview } = usePreview();
    // Debug logging
    (0,react.useEffect)(() => {
        debugLog('🔄 [PDF Builder] État bouton Enregistrer mis à jour', {
            templateName,
            buttonState: {
                disabled: deferredIsSaving || !deferredIsModified || deferredIsLoading,
                isSaving: deferredIsSaving,
                isModified: deferredIsModified,
                isLoading: deferredIsLoading,
                canSave: !deferredIsSaving && deferredIsModified && !deferredIsLoading
            },
            timestamp: new Date().toISOString()
        });
    }, [deferredIsSaving, deferredIsModified, deferredIsLoading, templateName]);
    (0,react.useEffect)(() => {
    }, [showPreviewModal]);
    // Synchroniser les états locaux avec les props quand elles changent
    (0,react.useEffect)(() => {
        setEditedTemplateName(templateName);
    }, [templateName]);
    (0,react.useEffect)(() => {
        setEditedTemplateDescription(templateDescription);
    }, [templateDescription]);
    (0,react.useEffect)(() => {
        setEditedCanvasWidth(canvasWidth);
    }, [canvasWidth]);
    (0,react.useEffect)(() => {
        setEditedCanvasHeight(canvasHeight);
    }, [canvasHeight]);
    (0,react.useEffect)(() => {
        setEditedShowGuides(showGuides);
    }, [showGuides]);
    (0,react.useEffect)(() => {
        // Si les guides sont désactivés globalement, forcer l'état local à false
        if (!canvasSettings.guidesEnabled) {
            setEditedShowGuides(false);
        }
    }, [canvasSettings.guidesEnabled]);
    (0,react.useEffect)(() => {
        setEditedSnapToGrid(snapToGrid);
    }, [snapToGrid]);
    // State pour le throttling du scroll
    const [scrollTimeout, setScrollTimeout] = (0,react.useState)(null);
    // Optimisation: mémoriser le handler de scroll avec throttling
    const handleScroll = (0,react.useCallback)(() => {
        if (scrollTimeout)
            return; // Si un timeout est déjà en cours, ignorer
        setScrollTimeout(setTimeout(() => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            // Le header devient fixe après 120px de scroll
            setIsHeaderFixed(scrollTop > 120);
            setScrollTimeout(null);
        }, 50)); // Délai de 50ms pour éviter les changements trop fréquents
    }, [scrollTimeout]);
    // Effet pour gérer le scroll et rendre le header fixe
    (0,react.useEffect)(() => {
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, [handleScroll]);
    // Effet pour fermer le dropdown des modèles prédéfinis quand on clique ailleurs
    (0,react.useEffect)(() => {
        const handleClickOutside = (event) => {
            const target = event.target;
            if (showPredefinedTemplates && !target.closest('[data-predefined-dropdown]')) {
                setShowPredefinedTemplates(false);
            }
        };
        if (showPredefinedTemplates) {
            document.addEventListener('mousedown', handleClickOutside, { passive: true });
            return () => document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [showPredefinedTemplates]);
    const buttonBaseStyles = {
        padding: '10px 16px',
        border: 'none',
        borderRadius: '6px',
        cursor: 'pointer',
        fontSize: '14px',
        fontWeight: '500',
        display: 'flex',
        alignItems: 'center',
        gap: '6px',
        whiteSpace: 'nowrap'
    };
    const primaryButtonStyles = {
        ...buttonBaseStyles,
        backgroundColor: '#4CAF50',
        color: '#fff',
        boxShadow: hoveredButton === 'save' ? '0 4px 12px rgba(76, 175, 80, 0.3)' : 'none'
    };
    const secondaryButtonStyles = {
        ...buttonBaseStyles,
        backgroundColor: '#fff',
        border: '1px solid #ddd',
        color: '#333',
        boxShadow: hoveredButton === 'preview-image' || hoveredButton === 'preview-pdf' || hoveredButton === 'new' ? '0 2px 8px rgba(0, 0, 0, 0.1)' : 'none'
    };
    return ((0,jsx_runtime.jsxs)("div", { style: {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            padding: isHeaderFixed ? '16px' : '12px',
            paddingLeft: isHeaderFixed ? '16px' : '12px',
            paddingRight: isHeaderFixed ? '16px' : '12px',
            backgroundColor: '#ffffff',
            borderBottom: '2px solid #e0e0e0',
            borderRadius: '0px',
            boxShadow: isHeaderFixed
                ? '0 4px 12px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1)'
                : 'none',
            gap: '16px',
            position: isHeaderFixed ? 'fixed' : 'relative',
            top: isHeaderFixed ? '32px' : 'auto',
            left: isHeaderFixed ? '160px' : 'auto',
            right: isHeaderFixed ? '0' : 'auto',
            width: isHeaderFixed ? 'calc(100% - 160px)' : 'auto',
            zIndex: 1000,
            boxSizing: 'border-box',
            transition: 'all 0.25s ease-in-out'
        }, children: [(0,jsx_runtime.jsx)("div", { style: {
                    display: 'flex',
                    alignItems: 'center',
                    gap: '12px',
                    minWidth: 0,
                    flex: 1
                }, children: (0,jsx_runtime.jsxs)("div", { style: {
                        display: 'flex',
                        alignItems: 'baseline',
                        gap: '12px',
                        minWidth: 0
                    }, children: [(0,jsx_runtime.jsx)("h2", { style: {
                                margin: 0,
                                fontSize: '20px',
                                fontWeight: '600',
                                color: '#1a1a1a',
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                whiteSpace: 'nowrap'
                            }, children: templateName || 'Sans titre' }), (0,jsx_runtime.jsxs)("div", { style: {
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px',
                                flexShrink: 0
                            }, children: [deferredIsModified && ((0,jsx_runtime.jsxs)("span", { style: {
                                        fontSize: '12px',
                                        padding: '4px 10px',
                                        backgroundColor: '#fff3cd',
                                        color: '#856404',
                                        borderRadius: '4px',
                                        fontWeight: '500',
                                        border: '1px solid #ffeaa7',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '4px'
                                    }, children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '16px' }, children: "\u25CF" }), "Modifi\u00E9"] })), isNewTemplate && ((0,jsx_runtime.jsx)("span", { style: {
                                        fontSize: '12px',
                                        padding: '4px 10px',
                                        backgroundColor: '#d1ecf1',
                                        color: '#0c5460',
                                        borderRadius: '4px',
                                        fontWeight: '500',
                                        border: '1px solid #bee5eb'
                                    }, children: "Nouveau" }))] })] }) }), (0,jsx_runtime.jsxs)("div", { style: {
                    display: 'flex',
                    gap: '10px',
                    flexShrink: 0,
                    alignItems: 'center'
                }, children: [(0,jsx_runtime.jsxs)("button", { onClick: onNewTemplate, onMouseEnter: () => setHoveredButton('new'), onMouseLeave: () => setHoveredButton(null), style: {
                            ...secondaryButtonStyles,
                            opacity: isSaving ? 0.6 : 1,
                            pointerEvents: isSaving ? 'none' : 'auto'
                        }, title: "Cr\u00E9er un nouveau template", children: [(0,jsx_runtime.jsx)("span", { children: "\u2795" }), (0,jsx_runtime.jsx)("span", { children: "Nouveau" })] }), (0,jsx_runtime.jsxs)("div", { style: { position: 'relative' }, "data-predefined-dropdown": true, children: [(0,jsx_runtime.jsxs)("button", { onClick: () => setShowPredefinedTemplates(!showPredefinedTemplates), onMouseEnter: () => setHoveredButton('predefined'), onMouseLeave: () => setHoveredButton(null), style: {
                                    ...secondaryButtonStyles,
                                    opacity: isSaving ? 0.6 : 1,
                                    pointerEvents: isSaving ? 'none' : 'auto'
                                }, title: "Mod\u00E8les pr\u00E9d\u00E9finis", children: [(0,jsx_runtime.jsx)("span", { children: "\uD83C\uDFA8" }), (0,jsx_runtime.jsx)("span", { children: "Mod\u00E8les Pr\u00E9d\u00E9finis" }), (0,jsx_runtime.jsx)("span", { style: { marginLeft: '4px', fontSize: '12px' }, children: "\u25BC" })] }), showPredefinedTemplates && ((0,jsx_runtime.jsxs)("div", { style: {
                                    position: 'absolute',
                                    top: '100%',
                                    right: 0,
                                    background: 'white',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '8px',
                                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                                    zIndex: 1001,
                                    minWidth: '280px',
                                    maxHeight: '400px',
                                    overflowY: 'auto'
                                }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                            padding: '12px 16px',
                                            borderBottom: '1px solid #e0e0e0',
                                            background: '#f8f9fa',
                                            fontWeight: '600',
                                            fontSize: '14px',
                                            color: '#23282d'
                                        }, children: "\uD83C\uDFA8 Mod\u00E8les Pr\u00E9d\u00E9finis" }), (0,jsx_runtime.jsxs)("div", { style: { padding: '8px 0' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                                                    padding: '12px 16px',
                                                    cursor: 'pointer',
                                                    borderBottom: '1px solid #f0f0f0',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    gap: '12px'
                                                }, onClick: () => {
                                                    // Ouvrir la page des templates prédéfinis
                                                    window.open('/wp-admin/admin.php?page=pdf-builder-templates', '_blank');
                                                    setShowPredefinedTemplates(false);
                                                }, onMouseEnter: (e) => e.currentTarget.style.backgroundColor = '#f8f9fa', onMouseLeave: (e) => e.currentTarget.style.backgroundColor = 'transparent', children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '20px' }, children: "\uD83E\uDDFE" }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("div", { style: { fontWeight: '500', color: '#23282d' }, children: "Facture Professionnelle" }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '12px', color: '#666' }, children: "Template professionnel pour factures" })] })] }), (0,jsx_runtime.jsxs)("div", { style: {
                                                    padding: '12px 16px',
                                                    cursor: 'pointer',
                                                    borderBottom: '1px solid #f0f0f0',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    gap: '12px'
                                                }, onClick: () => {
                                                    // Ouvrir la page des templates prédéfinis
                                                    window.open('/wp-admin/admin.php?page=pdf-builder-templates', '_blank');
                                                    setShowPredefinedTemplates(false);
                                                }, onMouseEnter: (e) => e.currentTarget.style.backgroundColor = '#f8f9fa', onMouseLeave: (e) => e.currentTarget.style.backgroundColor = 'transparent', children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '20px' }, children: "\uD83D\uDCCB" }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("div", { style: { fontWeight: '500', color: '#23282d' }, children: "Devis Commercial" }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '12px', color: '#666' }, children: "Template professionnel pour devis" })] })] }), (0,jsx_runtime.jsxs)("div", { style: {
                                                    padding: '12px 16px',
                                                    cursor: 'pointer',
                                                    borderBottom: '1px solid #f0f0f0',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    gap: '12px'
                                                }, onClick: () => {
                                                    // Ouvrir la page des templates prédéfinis
                                                    window.open('/wp-admin/admin.php?page=pdf-builder-templates', '_blank');
                                                    setShowPredefinedTemplates(false);
                                                }, onMouseEnter: (e) => e.currentTarget.style.backgroundColor = '#f8f9fa', onMouseLeave: (e) => e.currentTarget.style.backgroundColor = 'transparent', children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '20px' }, children: "\uD83D\uDCE6" }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("div", { style: { fontWeight: '500', color: '#23282d' }, children: "Bon de Commande" }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '12px', color: '#666' }, children: "Template professionnel pour commandes" })] })] }), (0,jsx_runtime.jsxs)("div", { style: {
                                                    padding: '12px 16px',
                                                    cursor: 'pointer',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    gap: '12px',
                                                    color: '#007cba',
                                                    fontWeight: '500'
                                                }, onClick: () => {
                                                    // Ouvrir la page des templates prédéfinis
                                                    window.open('/wp-admin/admin.php?page=pdf-builder-templates', '_blank');
                                                    setShowPredefinedTemplates(false);
                                                }, onMouseEnter: (e) => e.currentTarget.style.backgroundColor = '#f8f9fa', onMouseLeave: (e) => e.currentTarget.style.backgroundColor = 'transparent', children: [(0,jsx_runtime.jsx)("span", { style: { fontSize: '16px' }, children: "\uD83D\uDCDA" }), (0,jsx_runtime.jsx)("span", { children: "Voir tous les mod\u00E8les..." })] })] })] }))] }), (0,jsx_runtime.jsxs)("button", { onClick: () => {
                            openPreviewModal();
                        }, onMouseEnter: () => setHoveredButton('preview'), onMouseLeave: () => setHoveredButton(null), style: {
                            ...secondaryButtonStyles,
                            opacity: isSaving ? 0.6 : 1,
                            pointerEvents: isSaving ? 'none' : 'auto'
                        }, title: "G\u00E9n\u00E9rer un aper\u00E7u du PDF (Image ou PDF)", children: [(0,jsx_runtime.jsx)("span", { children: "\uD83D\uDC41\uFE0F" }), (0,jsx_runtime.jsx)("span", { children: "Aper\u00E7u" })] }), (0,jsx_runtime.jsx)("div", { style: { width: '1px', height: '24px', backgroundColor: '#e0e0e0' } }), (0,jsx_runtime.jsxs)("button", { onClick: () => setShowJsonModal(true), onMouseEnter: () => setHoveredButton('json'), onMouseLeave: () => setHoveredButton(null), style: {
                            ...secondaryButtonStyles,
                            opacity: isSaving ? 0.6 : 1,
                            pointerEvents: isSaving ? 'none' : 'auto'
                        }, title: "Voir et copier le JSON du canvas", children: [(0,jsx_runtime.jsx)("span", { children: "\uD83D\uDCC4" }), (0,jsx_runtime.jsx)("span", { children: "JSON" })] }), (0,jsx_runtime.jsxs)("button", { onClick: () => setShowSettingsModal(true), onMouseEnter: () => setHoveredButton('settings'), onMouseLeave: () => setHoveredButton(null), style: {
                            ...secondaryButtonStyles,
                            opacity: isSaving ? 0.6 : 1,
                            pointerEvents: isSaving ? 'none' : 'auto'
                        }, title: "Param\u00E8tres du template", children: [(0,jsx_runtime.jsx)("span", { children: "\u2699\uFE0F" }), (0,jsx_runtime.jsx)("span", { children: "Param\u00E8tres" })] }), (0,jsx_runtime.jsxs)("button", { onClick: async () => {
                            var _a, _b, _c, _d, _e;
                            const startTime = performance.now();
                            debugLog('🚀 [PDF Builder] Bouton Enregistrer cliqué', {
                                templateName,
                                isModified: deferredIsModified,
                                isSaving: deferredIsSaving,
                                isLoading: deferredIsLoading,
                                timestamp: new Date().toISOString(),
                                // Informations détaillées sur le canvas
                                canvasInfo: {
                                    width: canvasWidth,
                                    height: canvasHeight,
                                    showGuides,
                                    snapToGrid
                                },
                                // Informations sur les éléments
                                elementsInfo: {
                                    totalElements: ((_a = state.elements) === null || _a === void 0 ? void 0 : _a.length) || 0,
                                    elementTypes: ((_b = state.elements) === null || _b === void 0 ? void 0 : _b.reduce((acc, el) => {
                                        acc[el.type] = (acc[el.type] || 0) + 1;
                                        return acc;
                                    }, {})) || {}
                                },
                                // État du builder
                                builderState: {
                                    template: state.template ? {
                                        name: state.template.name,
                                        description: state.template.description,
                                        hasBackground: !!state.canvas.backgroundColor
                                    } : null,
                                    selectedElement: state.selection.selectedElements[0] || null,
                                    zoom: state.canvas.zoom || 1
                                },
                                // Paramètres canvas
                                canvasSettings: {
                                    guidesEnabled: canvasSettings.guidesEnabled,
                                    memoryLimit: canvasSettings.memoryLimitJs
                                }
                            });
                            try {
                                debugLog('⏳ [PDF Builder] Début de la sauvegarde...');
                                await onSave();
                                const endTime = performance.now();
                                const saveDuration = endTime - startTime;
                                debugLog('✅ [PDF Builder] Sauvegarde réussie', {
                                    templateName,
                                    timestamp: new Date().toISOString(),
                                    duration: `${saveDuration.toFixed(2)}ms`,
                                    performance: {
                                        saveTime: saveDuration,
                                        elementsCount: ((_c = state.elements) === null || _c === void 0 ? void 0 : _c.length) || 0,
                                        templateSize: JSON.stringify(state.template).length,
                                        elementsSize: JSON.stringify(state.elements).length
                                    },
                                    // Vérification post-sauvegarde
                                    postSaveState: {
                                        isModified: false,
                                        isSaving: false
                                    }
                                });
                                // Log des métriques de performance
                                debugLog('📊 [PDF Builder] Métriques de sauvegarde', {
                                    duration: saveDuration,
                                    avgTimePerElement: ((_d = state.elements) === null || _d === void 0 ? void 0 : _d.length) ? saveDuration / state.elements.length : 0,
                                    memoryUsage: performance.memory ? {
                                        used: performance.memory.usedJSHeapSize,
                                        total: performance.memory.totalJSHeapSize,
                                        limit: performance.memory.jsHeapSizeLimit
                                    } : 'N/A'
                                });
                            }
                            catch (error) {
                                const endTime = performance.now();
                                const failedDuration = endTime - startTime;
                                debug_debugError('❌ [PDF Builder] Erreur lors de la sauvegarde:', {
                                    error: error instanceof Error ? {
                                        message: error.message,
                                        stack: error.stack,
                                        name: error.name
                                    } : error,
                                    templateName,
                                    timestamp: new Date().toISOString(),
                                    duration: `${failedDuration.toFixed(2)}ms`,
                                    context: {
                                        isModified: deferredIsModified,
                                        isSaving: deferredIsSaving,
                                        elementsCount: ((_e = state.elements) === null || _e === void 0 ? void 0 : _e.length) || 0
                                    }
                                });
                                alert('Erreur lors de la sauvegarde: ' + (error instanceof Error ? error.message : 'Erreur inconnue'));
                            }
                        }, disabled: deferredIsSaving || !deferredIsModified || deferredIsLoading, onMouseEnter: () => {
                            debugLog('👆 [PDF Builder] Souris sur bouton Enregistrer', {
                                templateName,
                                buttonState: {
                                    disabled: deferredIsSaving || !deferredIsModified || deferredIsLoading,
                                    isSaving: deferredIsSaving,
                                    isModified: deferredIsModified,
                                    isLoading: deferredIsLoading
                                },
                                timestamp: new Date().toISOString()
                            });
                            setHoveredButton('save');
                        }, onMouseLeave: () => {
                            debugLog('👋 [PDF Builder] Souris quitte bouton Enregistrer', {
                                templateName,
                                timestamp: new Date().toISOString()
                            });
                            setHoveredButton(null);
                        }, style: {
                            ...primaryButtonStyles,
                            opacity: (deferredIsSaving || !deferredIsModified || deferredIsLoading) ? 0.6 : 1,
                            pointerEvents: (deferredIsSaving || !deferredIsModified || deferredIsLoading) ? 'none' : 'auto'
                        }, title: deferredIsLoading ? 'Chargement du template...' :
                            deferredIsModified ? (deferredIsEditingExistingTemplate ? 'Modifier le template' : 'Enregistrer les modifications') :
                                'Aucune modification', children: [(0,jsx_runtime.jsx)("span", { children: deferredIsSaving ? '⟳' : '💾' }), (0,jsx_runtime.jsx)("span", { children: deferredIsSaving ? 'Enregistrement...' : (deferredIsEditingExistingTemplate ? 'Modifier' : 'Enregistrer') })] })] }), showSettingsModal && ((0,jsx_runtime.jsx)("div", { style: {
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(0, 0, 0, 0.5)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 1000
                }, children: (0,jsx_runtime.jsxs)("div", { style: {
                        backgroundColor: '#ffffff',
                        borderRadius: '8px',
                        padding: '24px',
                        maxWidth: '500px',
                        width: '90%',
                        maxHeight: '80vh',
                        overflowY: 'auto',
                        boxShadow: '0 10px 30px rgba(0, 0, 0, 0.3)'
                    }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                                display: 'flex',
                                justifyContent: 'space-between',
                                alignItems: 'center',
                                marginBottom: '20px',
                                borderBottom: '1px solid #e0e0e0',
                                paddingBottom: '16px'
                            }, children: [(0,jsx_runtime.jsx)("h3", { style: { margin: 0, fontSize: '18px', fontWeight: '600', color: '#1a1a1a' }, children: "Param\u00E8tres du template" }), (0,jsx_runtime.jsx)("button", { onClick: () => setShowSettingsModal(false), style: {
                                        background: 'none',
                                        border: 'none',
                                        fontSize: '24px',
                                        cursor: 'pointer',
                                        color: '#666',
                                        padding: '4px'
                                    }, title: "Fermer", children: "\u00D7" })] }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', flexDirection: 'column', gap: '16px' }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }, children: "Nom du mod\u00E8le" }), (0,jsx_runtime.jsx)("input", { type: "text", value: editedTemplateName, onChange: (e) => setEditedTemplateName(e.target.value), style: {
                                                width: '100%',
                                                padding: '8px 12px',
                                                border: '1px solid #ddd',
                                                borderRadius: '4px',
                                                fontSize: '14px',
                                                backgroundColor: '#ffffff'
                                            }, placeholder: "Entrez le nom du template" })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }, children: "Description" }), (0,jsx_runtime.jsx)("textarea", { value: editedTemplateDescription, onChange: (e) => setEditedTemplateDescription(e.target.value), style: {
                                                width: '100%',
                                                padding: '8px 12px',
                                                border: '1px solid #ddd',
                                                borderRadius: '4px',
                                                fontSize: '14px',
                                                minHeight: '60px',
                                                resize: 'vertical'
                                            }, placeholder: "Description du template..." })] }), (0,jsx_runtime.jsxs)("div", { style: { borderTop: '1px solid #e0e0e0', paddingTop: '16px', marginTop: '16px' }, children: [(0,jsx_runtime.jsx)("h4", { style: { margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#333' }, children: "Param\u00E8tres avanc\u00E9s" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }, children: [(0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#555' }, children: "Largeur du canvas (px)" }), (0,jsx_runtime.jsx)("input", { type: "number", value: editedCanvasWidth, disabled: true, style: {
                                                                width: '100%',
                                                                padding: '6px 8px',
                                                                border: '1px solid #ccc',
                                                                borderRadius: '3px',
                                                                fontSize: '12px',
                                                                backgroundColor: '#f5f5f5',
                                                                color: '#999',
                                                                cursor: 'not-allowed'
                                                            } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#999', marginTop: '2px' }, children: "Non modifiable" })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#555' }, children: "Hauteur du canvas (px)" }), (0,jsx_runtime.jsx)("input", { type: "number", value: editedCanvasHeight, disabled: true, style: {
                                                                width: '100%',
                                                                padding: '6px 8px',
                                                                border: '1px solid #ccc',
                                                                borderRadius: '3px',
                                                                fontSize: '12px',
                                                                backgroundColor: '#f5f5f5',
                                                                color: '#999',
                                                                cursor: 'not-allowed'
                                                            } }), (0,jsx_runtime.jsx)("div", { style: { fontSize: '10px', color: '#999', marginTop: '2px' }, children: "Non modifiable" })] })] }), (0,jsx_runtime.jsx)("div", { style: { marginTop: '12px' }, children: (0,jsx_runtime.jsxs)("label", { style: {
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    gap: '8px',
                                                    fontSize: '12px',
                                                    fontWeight: '500',
                                                    color: canvasSettings.guidesEnabled ? '#555' : '#999',
                                                    opacity: canvasSettings.guidesEnabled ? 1 : 0.6
                                                }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: editedShowGuides, onChange: (e) => setEditedShowGuides(e.target.checked), disabled: !canvasSettings.guidesEnabled, style: { margin: 0 } }), "Afficher les guides d'alignement", !canvasSettings.guidesEnabled && ((0,jsx_runtime.jsx)("span", { style: { fontSize: '10px', color: '#999', fontStyle: 'italic' }, children: "(d\u00E9sactiv\u00E9 dans les param\u00E8tres)" }))] }) }), (0,jsx_runtime.jsx)("div", { style: { marginTop: '8px' }, children: (0,jsx_runtime.jsxs)("label", { style: { display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12px', fontWeight: '500', color: '#555' }, children: [(0,jsx_runtime.jsx)("input", { type: "checkbox", checked: editedSnapToGrid, onChange: (e) => setEditedSnapToGrid(e.target.checked), style: { margin: 0 } }), "Mode grille magn\u00E9tique"] }) })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }, children: "Statut" }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' }, children: [isNewTemplate && ((0,jsx_runtime.jsx)("span", { style: {
                                                        padding: '4px 8px',
                                                        backgroundColor: '#e3f2fd',
                                                        color: '#1565c0',
                                                        borderRadius: '12px',
                                                        fontSize: '12px',
                                                        fontWeight: '500'
                                                    }, children: "Nouveau template" })), deferredIsModified && ((0,jsx_runtime.jsx)("span", { style: {
                                                        padding: '4px 8px',
                                                        backgroundColor: '#fff3e0',
                                                        color: '#f57c00',
                                                        borderRadius: '12px',
                                                        fontSize: '12px',
                                                        fontWeight: '500'
                                                    }, children: "Modifi\u00E9" })), isEditingExistingTemplate && ((0,jsx_runtime.jsx)("span", { style: {
                                                        padding: '4px 8px',
                                                        backgroundColor: '#f3e5f5',
                                                        color: '#7b1fa2',
                                                        borderRadius: '12px',
                                                        fontSize: '12px',
                                                        fontWeight: '500'
                                                    }, children: "\u00C9dition existante" }))] })] }), (0,jsx_runtime.jsxs)("div", { children: [(0,jsx_runtime.jsx)("label", { style: { display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }, children: "Informations syst\u00E8me" }), (0,jsx_runtime.jsxs)("div", { style: { fontSize: '13px', color: '#666', lineHeight: '1.5' }, children: [(0,jsx_runtime.jsxs)("div", { children: ["Template ID: ", templateName || 'N/A'] }), (0,jsx_runtime.jsxs)("div", { children: ["Derni\u00E8re modification: ", new Date().toLocaleString('fr-FR')] }), (0,jsx_runtime.jsxs)("div", { children: ["\u00C9tat: ", deferredIsSaving ? 'Enregistrement...' : deferredIsModified ? 'Modifié' : 'Sauvegardé'] })] })] }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', justifyContent: 'flex-end', gap: '12px', marginTop: '20px' }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => setShowSettingsModal(false), style: {
                                                padding: '8px 16px',
                                                border: '1px solid #ddd',
                                                borderRadius: '4px',
                                                backgroundColor: '#f8f8f8',
                                                color: '#333',
                                                cursor: 'pointer',
                                                fontSize: '14px'
                                            }, children: "Annuler" }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                                // Sauvegarder les paramètres du template
                                                onUpdateTemplateSettings({
                                                    name: editedTemplateName,
                                                    description: editedTemplateDescription,
                                                    showGuides: editedShowGuides,
                                                    snapToGrid: editedSnapToGrid
                                                });
                                                setShowSettingsModal(false);
                                            }, style: {
                                                padding: '8px 16px',
                                                border: 'none',
                                                borderRadius: '4px',
                                                backgroundColor: '#4CAF50',
                                                color: '#ffffff',
                                                cursor: 'pointer',
                                                fontSize: '14px',
                                                fontWeight: '500'
                                            }, children: "Sauvegarder" })] })] })] }) })), showJsonModal && ((0,jsx_runtime.jsx)("div", { style: {
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(0, 0, 0, 0.5)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 1001
                }, children: (0,jsx_runtime.jsxs)("div", { style: {
                        backgroundColor: '#ffffff',
                        borderRadius: '8px',
                        padding: '24px',
                        maxWidth: '90vw',
                        width: '100%',
                        maxHeight: '85vh',
                        display: 'flex',
                        flexDirection: 'column',
                        boxShadow: '0 10px 40px rgba(0, 0, 0, 0.3)'
                    }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                                display: 'flex',
                                justifyContent: 'space-between',
                                alignItems: 'center',
                                marginBottom: '16px',
                                borderBottom: '1px solid #e0e0e0',
                                paddingBottom: '12px'
                            }, children: [(0,jsx_runtime.jsxs)("h3", { style: { margin: 0, fontSize: '18px', fontWeight: '600', color: '#1a1a1a' }, children: ["\uD83D\uDCCB JSON Brut du Template (ID: ", templateName || 'N/A', ")"] }), (0,jsx_runtime.jsx)("button", { onClick: () => setShowJsonModal(false), style: {
                                        background: 'none',
                                        border: 'none',
                                        fontSize: '24px',
                                        cursor: 'pointer',
                                        color: '#666',
                                        padding: '4px'
                                    }, title: "Fermer", children: "\u00D7" })] }), (0,jsx_runtime.jsx)("div", { style: {
                                flex: 1,
                                overflow: 'auto',
                                backgroundColor: '#f5f5f5',
                                borderRadius: '6px',
                                padding: '16px',
                                fontFamily: "'Courier New', monospace",
                                fontSize: '12px',
                                lineHeight: '1.5',
                                color: '#333',
                                whiteSpace: 'pre-wrap',
                                wordBreak: 'break-word',
                                border: '1px solid #ddd',
                                marginBottom: '16px'
                            }, children: JSON.stringify({
                                ...state.template,
                                elements: state.elements
                            }, null, 2) }), (0,jsx_runtime.jsxs)("div", { style: {
                                display: 'flex',
                                gap: '12px',
                                justifyContent: 'flex-end',
                                alignItems: 'center'
                            }, children: [(0,jsx_runtime.jsx)("button", { onClick: () => {
                                        navigator.clipboard.writeText(JSON.stringify({
                                            ...state.template,
                                            elements: state.elements
                                        }, null, 2));
                                        setCopySuccess(true);
                                        setTimeout(() => setCopySuccess(false), 2000);
                                    }, style: {
                                        padding: '8px 16px',
                                        backgroundColor: '#0073aa',
                                        color: '#ffffff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '14px',
                                        fontWeight: '500',
                                        opacity: copySuccess ? 0.7 : 1
                                    }, title: "Copier le JSON", children: copySuccess ? '✅ Copié!' : '📋 Copier JSON' }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                        const jsonString = JSON.stringify({
                                            ...state.template,
                                            elements: state.elements
                                        }, null, 2);
                                        const blob = new Blob([jsonString], { type: 'application/json' });
                                        const url = URL.createObjectURL(blob);
                                        const link = document.createElement('a');
                                        link.href = url;
                                        link.download = `template-${templateName || 'export'}-${new Date().getTime()}.json`;
                                        link.click();
                                        URL.revokeObjectURL(url);
                                    }, style: {
                                        padding: '8px 16px',
                                        backgroundColor: '#10a37f',
                                        color: '#ffffff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '14px',
                                        fontWeight: '500'
                                    }, title: "T\u00E9l\u00E9charger le JSON", children: "\uD83D\uDCBE T\u00E9l\u00E9charger" }), (0,jsx_runtime.jsx)("button", { onClick: () => setShowJsonModal(false), style: {
                                        padding: '8px 16px',
                                        border: '1px solid #ddd',
                                        borderRadius: '4px',
                                        backgroundColor: '#f8f8f8',
                                        color: '#333',
                                        cursor: 'pointer',
                                        fontSize: '14px',
                                        fontWeight: '500'
                                    }, children: "Fermer" })] })] }) })), showPreviewModal && ((0,jsx_runtime.jsx)("div", { style: {
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(0, 0, 0, 0.5)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 1001
                }, children: (0,jsx_runtime.jsxs)("div", { style: {
                        backgroundColor: '#ffffff',
                        borderRadius: '8px',
                        padding: '24px',
                        maxWidth: '90vw',
                        width: '600px',
                        maxHeight: '90vh',
                        overflow: 'auto',
                        boxShadow: '0 4px 20px rgba(0, 0, 0, 0.15)'
                    }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                                display: 'flex',
                                justifyContent: 'space-between',
                                alignItems: 'center',
                                marginBottom: '20px'
                            }, children: [(0,jsx_runtime.jsx)("h3", { style: {
                                        margin: 0,
                                        fontSize: '18px',
                                        fontWeight: '600',
                                        color: '#1a1a1a'
                                    }, children: "Aper\u00E7u du PDF" }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                        closePreviewModal();
                                        clearPreview();
                                    }, style: {
                                        background: 'none',
                                        border: 'none',
                                        fontSize: '24px',
                                        cursor: 'pointer',
                                        color: '#666',
                                        padding: '0',
                                        width: '30px',
                                        height: '30px',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center'
                                    }, title: "Fermer", children: "\u00D7" })] }), (0,jsx_runtime.jsxs)("div", { style: { marginBottom: '20px' }, children: [(0,jsx_runtime.jsx)("label", { style: {
                                        display: 'block',
                                        fontSize: '14px',
                                        fontWeight: '500',
                                        color: '#333',
                                        marginBottom: '8px'
                                    }, children: "Format d'export :" }), (0,jsx_runtime.jsx)("div", { style: { display: 'flex', gap: '10px' }, children: [
                                        { value: 'png', label: 'PNG', icon: '🖼️' },
                                        { value: 'jpg', label: 'JPG', icon: '📷' },
                                        { value: 'pdf', label: 'PDF', icon: '📄' }
                                    ].map(format => ((0,jsx_runtime.jsxs)("button", { onClick: () => setPreviewFormat(format.value), style: {
                                            padding: '8px 16px',
                                            border: `2px solid ${previewFormat === format.value ? '#007cba' : '#ddd'}`,
                                            borderRadius: '6px',
                                            backgroundColor: previewFormat === format.value ? '#f0f8ff' : '#fff',
                                            color: previewFormat === format.value ? '#007cba' : '#333',
                                            cursor: 'pointer',
                                            fontSize: '14px',
                                            fontWeight: '500',
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: '6px'
                                        }, children: [(0,jsx_runtime.jsx)("span", { children: format.icon }), (0,jsx_runtime.jsx)("span", { children: format.label })] }, format.value))) })] }), (0,jsx_runtime.jsx)("div", { style: { marginBottom: '20px' }, children: (0,jsx_runtime.jsx)("button", { onClick: async () => {
                                    await generatePreview({
                                        ...state.template,
                                        elements: state.elements
                                    }, {
                                        format: previewFormat,
                                        quality: 150
                                    });
                                }, disabled: isGeneratingPreview, style: {
                                    padding: '12px 24px',
                                    backgroundColor: isGeneratingPreview ? '#ccc' : '#007cba',
                                    color: '#fff',
                                    border: 'none',
                                    borderRadius: '6px',
                                    cursor: isGeneratingPreview ? 'not-allowed' : 'pointer',
                                    fontSize: '16px',
                                    fontWeight: '500',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: '8px'
                                }, children: isGeneratingPreview ? ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("span", { children: "\u27F3" }), (0,jsx_runtime.jsx)("span", { children: "G\u00E9n\u00E9ration en cours..." })] })) : ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [(0,jsx_runtime.jsx)("span", { children: "\uD83C\uDFA8" }), (0,jsx_runtime.jsx)("span", { children: "G\u00E9n\u00E9rer l'aper\u00E7u" })] })) }) }), previewError && ((0,jsx_runtime.jsxs)("div", { style: {
                                padding: '12px',
                                backgroundColor: '#f8d7da',
                                border: '1px solid #f5c6cb',
                                borderRadius: '4px',
                                color: '#721c24',
                                marginBottom: '20px'
                            }, children: [(0,jsx_runtime.jsx)("strong", { children: "Erreur:" }), " ", previewError] })), previewImageUrl && ((0,jsx_runtime.jsxs)("div", { style: { textAlign: 'center' }, children: [(0,jsx_runtime.jsx)("img", { src: previewImageUrl, alt: "Aper\u00E7u du PDF", style: {
                                        maxWidth: '100%',
                                        maxHeight: '400px',
                                        border: '1px solid #ddd',
                                        borderRadius: '4px',
                                        boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)'
                                    } }), (0,jsx_runtime.jsx)("div", { style: { marginTop: '10px' }, children: (0,jsx_runtime.jsx)("a", { href: previewImageUrl, download: `apercu-${templateName || 'template'}.${previewFormat}`, style: {
                                            padding: '8px 16px',
                                            backgroundColor: '#28a745',
                                            color: '#fff',
                                            textDecoration: 'none',
                                            borderRadius: '4px',
                                            fontSize: '14px',
                                            fontWeight: '500'
                                        }, children: "\uD83D\uDCBE T\u00E9l\u00E9charger" }) })] }))] }) }))] }));
});

;// ./assets/js/pdf-builder-react/components/element-library/ElementLibrary.tsx



// Définition des éléments WooCommerce (migration depuis l'ancien éditeur)
const WOOCOMMERCE_ELEMENTS = [
    {
        type: 'product_table',
        label: 'Tableau Produits',
        icon: '📋',
        description: 'Tableau des produits commandés avec quantités et prix',
        category: 'woocommerce',
        defaultProps: {
            x: 50,
            y: 50,
            width: 500,
            height: 200,
            showHeaders: true,
            showBorders: true,
            showAlternatingRows: true,
            showSku: false,
            showDescription: false,
            showQuantity: true,
            fontSize: 11,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            currency: '€',
            tableStyle: 'default',
            textAlign: 'left',
            verticalAlign: 'top',
            backgroundColor: '#ffffff',
            headerBackgroundColor: '#f9fafb',
            headerTextColor: '#111827',
            alternateRowColor: '#f9fafb',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            textColor: '#374151',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'customer_info',
        label: 'Fiche Client',
        icon: '👤',
        description: 'Informations détaillées du client (nom, adresse, email)',
        category: 'woocommerce',
        defaultProps: {
            x: 50,
            y: 220,
            width: 250,
            height: 120,
            showHeaders: true,
            showBackground: true,
            showBorders: false,
            showFullName: true,
            showAddress: true,
            showEmail: true,
            showPhone: true,
            showPaymentMethod: false,
            showTransactionId: false,
            layout: 'vertical',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            headerFontStyle: 'normal',
            bodyFontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            backgroundColor: '#e5e7eb',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#374151',
            headerTextColor: '#111827',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'company_info',
        label: 'Informations Entreprise',
        icon: '[D]',
        description: 'Nom, adresse, contact et TVA de l\'entreprise',
        category: 'woocommerce',
        defaultProps: {
            x: 50,
            y: 340,
            width: 250,
            height: 120,
            showBorders: false,
            showFullName: true,
            showAddress: true,
            showEmail: true,
            showPhone: true,
            showSiret: true,
            showVat: true,
            showRcs: true,
            showCapital: true,
            showCompanyName: true,
            layout: 'vertical',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            headerFontStyle: 'normal',
            bodyFontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            backgroundColor: '#e5e7eb',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#374151',
            headerTextColor: '#111827',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            showBackground: true,
            visible: true
        }
    },
    {
        type: 'company_logo',
        label: 'Logo Entreprise',
        icon: '🏢',
        description: 'Logo et identité visuelle de l\'entreprise',
        category: 'woocommerce',
        defaultProps: {
            x: 350,
            y: 50,
            width: 150,
            height: 80,
            fit: 'contain',
            objectFit: 'contain',
            alignment: 'left',
            src: '',
            backgroundColor: 'transparent',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            borderRadius: 0,
            opacity: 1,
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'order-number',
        label: 'Numéro de Commande',
        icon: '🔢',
        description: 'Référence de commande avec date',
        category: 'woocommerce',
        defaultProps: {
            x: 350,
            y: 130,
            width: 100,
            height: 30,
            fontSize: 14,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            textAlign: 'right',
            backgroundColor: 'transparent',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#374151',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'woocommerce-order-date',
        label: 'Date de Commande',
        icon: '📅',
        description: 'Date de création de la commande',
        category: 'woocommerce',
        defaultProps: {
            x: 350,
            y: 160,
            width: 100,
            height: 30,
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            textAlign: 'right',
            backgroundColor: 'transparent',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#374151',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'woocommerce-invoice-number',
        label: 'Numéro de Facture',
        icon: '📄',
        description: 'Numéro de facture généré',
        category: 'woocommerce',
        defaultProps: {
            x: 350,
            y: 190,
            width: 100,
            height: 30,
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            textAlign: 'right',
            backgroundColor: 'transparent',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#374151',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'document_type',
        label: 'Type de Document',
        icon: '📄',
        description: 'Type du document (Facture, Devis, Bon de commande, etc.)',
        category: 'woocommerce',
        defaultProps: {
            x: 50,
            y: 430,
            width: 150,
            height: 40,
            title: 'FACTURE',
            fontSize: 18,
            fontFamily: 'Arial',
            fontWeight: 'bold',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            textAlign: 'left',
            backgroundColor: 'transparent',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#111827',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'dynamic-text',
        label: 'Texte Dynamique',
        icon: '📝',
        description: 'Texte avec variables dynamiques',
        category: 'woocommerce',
        defaultProps: {
            x: 50,
            y: 550,
            width: 200,
            height: 40,
            text: 'Texte personnalisable',
            textTemplate: 'custom',
            autoWrap: true,
            theme: 'clean',
            fontSize: 14,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.3',
            backgroundColor: 'transparent',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    },
    {
        type: 'mentions',
        label: 'Mentions légales',
        icon: '📄',
        description: 'Informations légales (email, SIRET, téléphone, etc.)',
        category: 'woocommerce',
        defaultProps: {
            x: 50,
            y: 480,
            width: 500,
            height: 60,
            showEmail: true,
            showPhone: true,
            showSiret: true,
            showVat: true,
            separator: ' • ',
            fontSize: 10,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            fontStyle: 'normal',
            textDecoration: 'none',
            textTransform: 'none',
            letterSpacing: 'normal',
            wordSpacing: 'normal',
            lineHeight: '1.2',
            textAlign: 'left',
            backgroundColor: 'transparent',
            borderColor: '#e5e7eb',
            borderWidth: 0,
            textColor: '#6b7280',
            rotation: 0,
            shadowColor: '#000000',
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            shadowBlur: 0,
            visible: true
        }
    }
];
function ElementLibrary({ onElementSelect, className }) {
    const isMobile = useResponsive_useIsMobile();
    const isTablet = useResponsive_useIsTablet();
    const handleElementClick = (elementType) => {
        if (onElementSelect) {
            onElementSelect(elementType);
        }
    };
    const handleDragStart = (e, element) => {
        // Stocker les données de l'élément dans le transfert
        e.dataTransfer.setData('application/json', JSON.stringify({
            type: element.type,
            label: element.label,
            defaultProps: element.defaultProps
        }));
        e.dataTransfer.effectAllowed = 'copy';
    };
    const handleDragEnd = (_e) => {
        // Drag terminé
    };
    return ((0,jsx_runtime.jsx)(ResponsiveContainer, { className: `pdf-element-library ${className || ''}`, mobileClass: "element-library-mobile", tabletClass: "element-library-tablet", desktopClass: "element-library-desktop", children: (0,jsx_runtime.jsxs)("div", { style: {
                width: isMobile ? '100%' : isTablet ? '240px' : '280px',
                height: '100%',
                backgroundColor: '#f8f9fa',
                borderRight: isMobile ? 'none' : '1px solid #e9ecef',
                borderBottom: isMobile ? '1px solid #e9ecef' : 'none',
                display: 'flex',
                flexDirection: 'column',
                overflow: 'hidden'
            }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                        padding: isMobile ? '12px' : '16px',
                        borderBottom: '1px solid #e9ecef',
                        backgroundColor: '#ffffff'
                    }, children: [(0,jsx_runtime.jsx)("h3", { style: {
                                margin: 0,
                                fontSize: isMobile ? '14px' : '16px',
                                fontWeight: '600',
                                color: '#495057'
                            }, children: "\uD83D\uDCE6 \u00C9l\u00E9ments WooCommerce" }), (0,jsx_runtime.jsx)("p", { style: {
                                margin: '4px 0 0 0',
                                fontSize: isMobile ? '11px' : '12px',
                                color: '#6c757d',
                                display: isMobile ? 'none' : 'block'
                            }, children: "Glissez les \u00E9l\u00E9ments sur le canvas" })] }), (0,jsx_runtime.jsx)("div", { style: {
                        flex: 1,
                        overflowY: 'auto',
                        padding: isMobile ? '12px' : '8px'
                    }, children: (0,jsx_runtime.jsx)("div", { style: {
                            display: 'grid',
                            gap: '8px'
                        }, children: WOOCOMMERCE_ELEMENTS.map((element) => ((0,jsx_runtime.jsxs)("div", { draggable: true, onClick: () => handleElementClick(element.type), onDragStart: (e) => handleDragStart(e, element), onDragEnd: handleDragEnd, style: {
                                padding: '12px',
                                backgroundColor: '#ffffff',
                                border: '1px solid #dee2e6',
                                borderRadius: '6px',
                                cursor: 'grab',
                                transition: 'all 0.2s ease',
                                display: 'flex',
                                alignItems: 'center',
                                gap: '12px',
                                userSelect: 'none'
                            }, onMouseEnter: (e) => {
                                e.currentTarget.style.borderColor = '#007acc';
                                e.currentTarget.style.boxShadow = '0 2px 4px rgba(0, 122, 204, 0.1)';
                                e.currentTarget.style.cursor = 'grabbing';
                            }, onMouseLeave: (e) => {
                                e.currentTarget.style.borderColor = '#dee2e6';
                                e.currentTarget.style.boxShadow = 'none';
                                e.currentTarget.style.cursor = 'grab';
                            }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                        fontSize: '20px',
                                        width: '32px',
                                        height: '32px',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        backgroundColor: '#f8f9fa',
                                        borderRadius: '4px'
                                    }, children: element.icon }), (0,jsx_runtime.jsxs)("div", { style: { flex: 1 }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                fontSize: '14px',
                                                fontWeight: '500',
                                                color: '#495057',
                                                marginBottom: '2px'
                                            }, children: element.label }), (0,jsx_runtime.jsx)("div", { style: {
                                                fontSize: '12px',
                                                color: '#6c757d',
                                                lineHeight: '1.3'
                                            }, children: element.description })] })] }, element.type))) }) }), (0,jsx_runtime.jsx)("div", { style: {
                        padding: '12px 16px',
                        borderTop: '1px solid #e9ecef',
                        backgroundColor: '#ffffff',
                        fontSize: '11px',
                        color: '#6c757d',
                        textAlign: 'center'
                    }, children: "Cliquez sur un \u00E9l\u00E9ment pour l'ajouter" })] }) }));
}

;// ./assets/js/pdf-builder-react/utils/elementNormalization.ts
/**
 * Normalisation robuste des éléments pour assurer la préservation complète des propriétés
 * C'est LE système central qui garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
 */

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
        debugWarn('❌ [NORMALIZE] Elements n\'est pas un array:', typeof elements);
        return [];
    }
    return elements.map((el, idx) => {
        if (!el || typeof el !== 'object') {
            debugWarn(`❌ [NORMALIZE] Element ${idx} invalide:`, el);
            return {};
        }
        const element = el;
        // Créer une copie COMPLÈTE (spread shallow)
        const normalized = {
            ...element,
            id: element.id || `element-${idx}`,
            type: element.type || 'unknown',
            x: Number(element.x) || 0,
            y: Number(element.y) || 0,
            width: Number(element.width) || 100,
            height: Number(element.height) || 100
        };
        return normalized;
    });
}
/**
 * FONCTION CRITIQUE: Prépare les éléments pour la sauvegarde
 * Assure que TOUT est sérialisable et complet
 */
function normalizeElementsBeforeSave(elements) {
    if (!Array.isArray(elements)) {
        debugWarn('❌ [SAVE NORMALIZE] Elements n\'est pas un array');
        return [];
    }
    return elements.map((el, idx) => {
        if (!el || typeof el !== 'object') {
            debugWarn(`❌ [SAVE NORMALIZE] Element ${idx} invalide`);
            return {};
        }
        // Créer une copie COMPLÈTE
        const normalized = {
            ...el
        };
        // Valider les champs critiques
        if (!normalized.id)
            normalized.id = `element-${idx}`;
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
        const serializable = {};
        Object.keys(normalized).forEach(key => {
            const value = normalized[key];
            const type = typeof value;
            // DEBUG: Log des propriétés spéciales
            if (key.includes('🎯') || key.includes('interactions') || key.includes('comportement') || key.includes('behavior')) {
                // console.log(`[NORMALIZE] Propriété spéciale détectée: ${key} (type: ${type}) =`, value);
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
                    debugWarn(`⚠️  [SAVE NORMALIZE] Propriété non sérialisable ${key} skippée`, value);
                }
            }
            else {
                // Propriétés rejetées (functions, etc.)
                debugWarn(`⚠️  [SAVE NORMALIZE] Propriété rejetée: ${key} (type: ${type})`);
            }
        });
        return serializable;
    });
}
/**
 * Valide que les propriétés critiques sont présentes
 */
function validateElementIntegrity(elements, elementType) {
    const elementsOfType = elements.filter(el => el.type === elementType);
    if (elementsOfType.length === 0) {
        return true; // Pas d'éléments de ce type
    }
    let allValid = true;
    elementsOfType.forEach((el, idx) => {
        const required = ['id', 'type', 'x', 'y', 'width', 'height'];
        const missing = required.filter(key => !(key in el));
        if (missing.length > 0) {
            debugError(`❌ [VALIDATE] Element ${idx} missing: ${missing.join(', ')}`);
            allValid = false;
        }
        if (elementType === 'order_number') {
            const hasContentAlign = 'contentAlign' in el;
            const hasLabelPosition = 'labelPosition' in el;
            if (!hasContentAlign || !hasLabelPosition) {
                allValid = false;
            }
        }
    });
    return allValid;
}
/**
 * Debug helper: affiche un rapport complet
 */
function debugElementState(elements, label) {
    // Debug function - logs removed for production
}

;// ./assets/js/pdf-builder-react/hooks/useTemplate.ts





function useTemplate() {
    const { state, dispatch } = useBuilder();
    const { canvasWidth, canvasHeight } = CanvasSettingsContext_useCanvasSettings();
    // Détecter si on est sur un template existant via l'URL ou les données localisées
    const getTemplateIdFromUrl = (0,react.useCallback)(() => {
        var _a;
        // Priorité 1: Utiliser le templateId des données PHP localisées
        if ((_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.templateId) {
            return window.pdfBuilderData.templateId.toString();
        }
        // Priorité 2: Utiliser le paramètre URL (pour compatibilité)
        const urlParams = new URLSearchParams(window.location.search);
        const urlTemplateId = urlParams.get('template_id');
        if (urlTemplateId) {
            return urlTemplateId;
        }
        return null;
    }, []);
    const isEditingExistingTemplate = () => {
        return getTemplateIdFromUrl() !== null;
    };
    // Charger un template existant
    const loadExistingTemplate = (0,react.useCallback)(async (templateId) => {
        var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l, _m, _o;
        console.log('🔄 [useTemplate] loadExistingTemplate called with templateId:', templateId);
        console.log('🔄 [useTemplate] window.pdfBuilderData at start:', window.pdfBuilderData);
        console.log('🔄 [useTemplate] window.pdfBuilderData?.ajaxUrl:', (_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.ajaxUrl);
        console.log('🔄 [useTemplate] window.pdfBuilderData?.nonce:', (_b = window.pdfBuilderData) === null || _b === void 0 ? void 0 : _b.nonce);
        console.log('🔄 [useTemplate] window keys containing pdfBuilder:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
        console.log('🔄 [useTemplate] window.pdfBuilderData?.existingTemplate:', (_c = window.pdfBuilderData) === null || _c === void 0 ? void 0 : _c.existingTemplate);
        console.log('🔄 [useTemplate] window.pdfBuilderData?.hasExistingData:', (_d = window.pdfBuilderData) === null || _d === void 0 ? void 0 : _d.hasExistingData);
        try {
            // ✅ PRIORITÉ: Utiliser les données localisées si disponibles (plus rapide et fiable)
            if (((_e = window.pdfBuilderData) === null || _e === void 0 ? void 0 : _e.existingTemplate) && ((_f = window.pdfBuilderData) === null || _f === void 0 ? void 0 : _f.hasExistingData)) {
                console.log('🔄 [useTemplate] USING LOCALIZED DATA PATH for template:', templateId);
                const templateData = window.pdfBuilderData.existingTemplate;
                console.log('🔄 [useTemplate] templateData:', templateData);
                console.log('🔄 [useTemplate] templateData.name:', templateData === null || templateData === void 0 ? void 0 : templateData.name);
                console.log('🔄 [useTemplate] templateData._db_name:', templateData === null || templateData === void 0 ? void 0 : templateData._db_name);
                console.log('🔄 [useTemplate] templateData keys:', Object.keys(templateData || {}));
                // Utiliser le nom du JSON en priorité (s'il existe et n'est pas vide), sinon le nom de la DB, sinon fallback explicite
                const templateName = ((templateData === null || templateData === void 0 ? void 0 : templateData.name) && templateData.name.trim() !== '') ?
                    templateData.name :
                    ((templateData === null || templateData === void 0 ? void 0 : templateData._db_name) && templateData._db_name.trim() !== '') ?
                        templateData._db_name :
                        `[NOM NON RÉCUPÉRÉ - ID: ${templateId}]`;
                console.log('🔄 [useTemplate] Final template name:', templateName);
                // console.log('📋 [LOAD TEMPLATE] Utilisation des données localisées pour template:', templateId, 'Nom:', templateData.name);
                // Parse JSON strings if needed
                let elements = [];
                let canvasData = null;
                try {
                    if (typeof templateData.elements === 'string') {
                        elements = JSON.parse(templateData.elements);
                    }
                    else if (Array.isArray(templateData.elements)) {
                        elements = templateData.elements;
                    }
                    else {
                        elements = [];
                    }
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
                        canvasData = { width: 210, height: 297 };
                    }
                }
                catch (parseError) {
                    debug_debugError('❌ [LOAD TEMPLATE] Erreur de parsing des données localisées:', parseError);
                    elements = [];
                    canvasData = { width: 210, height: 297 };
                }
                const normalizedElements = normalizeElementsAfterLoad(elements);
                const enrichedElements = normalizedElements.map((el) => {
                    let enrichedElement = { ...el };
                    if (el.type === 'company_logo' && !el.src && !el.logoUrl) {
                        const logoUrl = el.defaultSrc || '';
                        if (logoUrl) {
                            enrichedElement.src = logoUrl;
                        }
                    }
                    if (enrichedElement.createdAt) {
                        try {
                            const createdAt = new Date(enrichedElement.createdAt);
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
                            const updatedAt = new Date(enrichedElement.updatedAt);
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
                let lastSavedDate;
                try {
                    if (templateData.updated_at) {
                        lastSavedDate = new Date(templateData.updated_at);
                        if (isNaN(lastSavedDate.getTime())) {
                            lastSavedDate = new Date();
                        }
                    }
                    else {
                        lastSavedDate = new Date();
                    }
                }
                catch (_p) {
                    lastSavedDate = new Date();
                }
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
                return true;
            }
            // ✅ FALLBACK: Utiliser AJAX si les données localisées ne sont pas disponibles
            console.log('🔄 [useTemplate] USING AJAX FALLBACK PATH for template:', templateId);
            console.log('🔄 [useTemplate] Checking window.pdfBuilderData again:', window.pdfBuilderData);
            console.log('🔄 [useTemplate] ajaxUrl for AJAX call:', (_g = window.pdfBuilderData) === null || _g === void 0 ? void 0 : _g.ajaxUrl);
            console.log('🔄 [useTemplate] nonce for AJAX call:', (_h = window.pdfBuilderData) === null || _h === void 0 ? void 0 : _h.nonce);
            // Détecter le navigateur pour des en-têtes spécifiques
            const isChrome = typeof navigator !== 'undefined' &&
                /Chrome/.test(navigator.userAgent) &&
                /Google Inc/.test(navigator.vendor);
            const isFirefox = typeof navigator !== 'undefined' &&
                /Firefox/.test(navigator.userAgent);
            const isSafari = typeof navigator !== 'undefined' &&
                /Safari/.test(navigator.userAgent) &&
                !/Chrome/.test(navigator.userAgent) &&
                !/Chromium/.test(navigator.userAgent);
            console.log('🔄 [useTemplate] Browser detection:', { isChrome, isFirefox, isSafari });
            // Préparer les options fetch avec des en-têtes spécifiques par navigateur
            const fetchOptions = {
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
                console.log('🔄 [useTemplate] Using Chrome-specific options');
            }
            else if (isFirefox) {
                // Firefox gère bien le cache par défaut
                fetchOptions.cache = 'no-cache';
                console.log('🔄 [useTemplate] Using Firefox-specific options');
            }
            else if (isSafari) {
                // Safari peut avoir des problèmes avec certains modes
                fetchOptions.mode = 'cors';
                console.log('🔄 [useTemplate] Using Safari-specific options');
            }
            const cacheBreaker = Date.now();
            const url = `${(_j = window.pdfBuilderData) === null || _j === void 0 ? void 0 : _j.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${(_k = window.pdfBuilderData) === null || _k === void 0 ? void 0 : _k.nonce}&t=${cacheBreaker}`;
            console.log('🔄 [useTemplate] About to fetch URL:', url);
            console.log('🔄 [useTemplate] Fetch options:', fetchOptions);
            const response = await fetch(url, fetchOptions);
            if (!response.ok) {
                const errorText = await response.text();
                debug_debugError('[useTemplate] Response error text:', errorText);
                throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
            }
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.data || 'Erreur lors du chargement du template');
            }
            console.log('🔄 [useTemplate] AJAX result:', result);
            console.log('🔄 [useTemplate] result.data:', result.data);
            console.log('🔄 [useTemplate] result.template:', result.template);
            console.log('🔄 [useTemplate] result.template_name:', result.template_name);
            const templateData = result.data ? result.data.template : result.template;
            const ajaxTemplateName = result.data ? (result.data.template_name || result.data.name) : (result.name || result.template_name);
            console.log('🔄 [useTemplate] templateData:', templateData);
            console.log('🔄 [useTemplate] ajaxTemplateName:', ajaxTemplateName);
            // Appliquer la même logique de fallback que pour les données localisées
            const templateName = (ajaxTemplateName && ajaxTemplateName.trim() !== '') ?
                ajaxTemplateName :
                ((templateData === null || templateData === void 0 ? void 0 : templateData.name) && templateData.name.trim() !== '') ?
                    templateData.name :
                    `[NOM NON RÉCUPÉRÉ - ID: ${templateId}]`;
            // 🔍 Tracer les éléments reçus du serveur
            if (templateData.elements) {
                // 🔍 Vérifier spécifiquement les éléments order_number
                const orderNumberElements = templateData.elements.filter((el) => el.type === 'order_number');
            }
            // Parse JSON strings
            let elements = [];
            let canvasData = null;
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
                    canvasData = { width: 210, height: 297 };
                }
            }
            catch (parseError) {
                debug_debugError('❌ [LOAD TEMPLATE] Erreur de parsing:', parseError);
                elements = [];
                canvasData = { width: 210, height: 297 };
            }
            // ✅ NORMALISER LES ÉLÉMENTS APRÈS CHARGE (CRITIQUE!)
            // Cela garantit que contentAlign, labelPosition, etc. sont préservés
            const normalizedElements = normalizeElementsAfterLoad(elements);
            debugElementState(normalizedElements, 'APRÈS CHARGEMENT');
            // 🏷️ Enrichir les éléments company_logo avec src si manquant et convertir les dates
            const enrichedElements = normalizedElements.map((el) => {
                let enrichedElement = { ...el };
                // ✅ CORRECTION: Enrichir les éléments company_logo SEULEMENT si src ET logoUrl sont vides
                if (el.type === 'company_logo' && !el.src && !el.logoUrl) {
                    // Essayer d'obtenir le logo depuis les propriétés de l'élément
                    const logoUrl = el.defaultSrc || '';
                    if (logoUrl) {
                        enrichedElement.src = logoUrl;
                    }
                }
                // Convertir les propriétés de date en objets Date valides
                if (enrichedElement.createdAt) {
                    try {
                        const createdAt = new Date(enrichedElement.createdAt);
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
                        const updatedAt = new Date(enrichedElement.updatedAt);
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
            enrichedElements.slice(0, 3).forEach((_el, _idx) => {
            });
            // Créer une date valide pour lastSaved
            let lastSavedDate;
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
            catch (_q) {
                lastSavedDate = new Date();
            }
            // 🔍 Log final des éléments order_number avant envoi au contexte
            const finalOrderNumberElements = enrichedElements.filter((el) => el.type === 'order_number');
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
            return true;
        }
        catch (error) {
            debug_debugError('❌ [LOAD TEMPLATE] Erreur lors du chargement:', error);
            // Diagnostics spécifiques selon le navigateur
            const isChrome = typeof navigator !== 'undefined' &&
                /Chrome/.test(navigator.userAgent) &&
                /Google Inc/.test(navigator.vendor);
            const isFirefox = typeof navigator !== 'undefined' &&
                /Firefox/.test(navigator.userAgent);
            const isSafari = typeof navigator !== 'undefined' &&
                /Safari/.test(navigator.userAgent) &&
                !/Chrome/.test(navigator.userAgent) &&
                !/Chromium/.test(navigator.userAgent);
            debug_debugError(`❌ [LOAD TEMPLATE] Échec du chargement sur ${isChrome ? 'Chrome' : isFirefox ? 'Firefox' : isSafari ? 'Safari' : 'navigateur inconnu'}`);
            debug_debugError('❌ [LOAD TEMPLATE] Détails de l\'erreur:', {
                message: error instanceof Error ? error.message : 'Unknown error',
                stack: error instanceof Error ? error.stack : undefined,
                name: error instanceof Error ? error.name : 'Unknown',
                templateId: templateId,
                ajaxUrl: (_l = window.pdfBuilderData) === null || _l === void 0 ? void 0 : _l.ajaxUrl,
                userAgent: navigator.userAgent
            });
            // Tentative de fallback pour Chrome
            if (isChrome && (error instanceof Error && error.message.includes('fetch'))) {
                debugWarn('🔄 [LOAD TEMPLATE] Tentative de fallback pour Chrome - Nouvelle tentative avec options différentes');
                try {
                    // Attendre un peu avant retry
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    // Retry avec des options différentes
                    const fallbackOptions = {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json, text/plain, */*',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        mode: 'no-cors',
                        cache: 'reload'
                    };
                    const fallbackUrl = `${(_m = window.pdfBuilderData) === null || _m === void 0 ? void 0 : _m.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${(_o = window.pdfBuilderData) === null || _o === void 0 ? void 0 : _o.nonce}&fallback=1&t=${Date.now()}`;
                    const fallbackResponse = await fetch(fallbackUrl, fallbackOptions);
                    if (fallbackResponse.ok || fallbackResponse.status === 0) { // no-cors peut retourner status 0
                        // Traiter la réponse même si elle est opaque
                        return true;
                    }
                }
                catch (fallbackError) {
                    debug_debugError('❌ [LOAD TEMPLATE] Échec du fallback:', fallbackError);
                }
            }
            return false;
        }
    }, [dispatch]);
    // 🎯 DISABLED: Event-based template loading causes race conditions with useEffect
    // Both methods try to load the same template, causing flashing/alternating canvas
    // The useEffect approach (reading URL) is more reliable and runs once per page load
    (0,react.useEffect)(() => {
        // ✅ Event listener disabled to prevent race conditions
        // Only useEffect with URL reading will load templates now
        return () => {
            // cleanup
        };
    }, []);
    // Effet pour charger automatiquement un template existant au montage
    // ✅ Dépendance vide: charger une seule fois au montage du composant
    (0,react.useEffect)(() => {
        const templateId = getTemplateIdFromUrl();
        if (templateId) {
            // Timeout de sécurité : forcer isLoading à false après 10 secondes si le chargement échoue
            const loadingTimeout = setTimeout(() => {
                debug_debugError('[useTemplate] Loading timeout reached, forcing isLoading to false');
                dispatch({ type: 'SET_TEMPLATE_LOADING', payload: false });
            }, 10000);
            // Charger le template avec gestion d'erreur améliorée
            loadExistingTemplate(templateId)
                .then(() => {
                clearTimeout(loadingTimeout);
            })
                .catch((error) => {
                clearTimeout(loadingTimeout);
                debug_debugError('[useTemplate] Template loading failed:', error);
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
    const saveTemplate = (0,react.useCallback)(async () => {
        var _a, _b, _c, _d;
        // console.log('[PDF_BUILDER_FRONTEND] Starting template save...');
        dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });
        try {
            const templateId = getTemplateIdFromUrl();
            // console.log('[PDF_BUILDER_FRONTEND] Template ID:', templateId);
            if (!templateId) {
                throw new Error('Aucun template chargé pour la sauvegarde');
            }
            // Vérifier que le template est complètement chargé
            if (!state.template.name || state.template.name.trim() === '') {
                // console.log('[PDF_BUILDER_FRONTEND] Template name not loaded yet, skipping save');
                return; // Ne pas lancer d'erreur, juste ignorer
            }
            // console.log('[PDF_BUILDER_FRONTEND] Template name:', state.template.name);
            if (!((_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.ajaxUrl)) {
                throw new Error('URL AJAX non disponible');
            }
            if (!((_b = window.pdfBuilderData) === null || _b === void 0 ? void 0 : _b.nonce)) {
                throw new Error('Nonce non disponible');
            }
            // console.log('[PDF_BUILDER_FRONTEND] AJAX URL available:', !!window.pdfBuilderData?.ajaxUrl);
            // console.log('[PDF_BUILDER_FRONTEND] Nonce available:', !!window.pdfBuilderData?.nonce);
            // ✅ NORMALISER LES ÉLÉMENTS AVANT SAUVEGARDE
            // Cela garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
            const normalizedElements = normalizeElementsBeforeSave(state.elements);
            debugElementState(normalizedElements, 'AVANT SAUVEGARDE');
            // 🔍 DEBUG: Log complet des propriétés des éléments avant sauvegarde
            // console.log('[PDF_BUILDER_FRONTEND] Éléments avant normalisation:', state.elements);
            // console.log('[PDF_BUILDER_FRONTEND] Éléments après normalisation:', normalizedElements);
            // Vérifier les propriétés spéciales
            normalizedElements.forEach((el, idx) => {
                // console.log(`[PDF_BUILDER_FRONTEND] Élément ${idx} (${el.type}) propriétés:`, Object.keys(el));
                // Chercher des propriétés avec emoji ou "interactions"
                Object.keys(el).forEach(key => {
                    if (key.includes('🎯') || key.includes('interactions') || key.includes('comportement') || key.includes('behavior')) {
                        // console.log(`[PDF_BUILDER_FRONTEND] Propriété spéciale trouvée: ${key} =`, el[key]);
                    }
                });
            });
            // Structure simple et propre pour la sauvegarde
            const templateData = {
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
            const formData = new FormData();
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
            // console.log('[PDF_BUILDER_FRONTEND] Data to send:');
            // console.log('- Template ID:', templateId);
            // console.log('- Template Name:', state.template.name || 'Nouveau template');
            // console.log('- Elements count:', normalizedElements.length);
            // console.log('- Canvas size:', canvasWidth, 'x', canvasHeight);
            // console.log('- Template data size:', JSON.stringify(templateData).length, 'characters');
            // console.log('- Nonce:', window.pdfBuilderData?.nonce ? 'Present' : 'Missing');
            const response = await fetch(((_d = window.pdfBuilderData) === null || _d === void 0 ? void 0 : _d.ajaxUrl) || '', {
                method: 'POST',
                body: formData
            });
            // console.log('[PDF_BUILDER_FRONTEND] HTTP Response status:', response.status);
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            const result = await response.json();
            // console.log('[PDF_BUILDER_FRONTEND] Server response:', result);
            if (!result.success) {
                debug_debugError('[PDF_BUILDER_FRONTEND] Server returned error:', result.data);
                const errorMessage = result.data || 'Unknown error during save';
                throw new Error(errorMessage);
            }
            // console.log('[PDF_BUILDER_FRONTEND] Save successful! Template ID:', result.data?.template_id);
            dispatch({
                type: 'SAVE_TEMPLATE',
                payload: {
                    id: result.data.template_id || result.data.id,
                    name: result.data.name
                }
            });
        }
        catch (error) {
            debug_debugError('[PDF_BUILDER_FRONTEND] Save failed:', error);
            debug_debugError('[useTemplate] SAVE - Error:', error);
            throw error;
        }
        finally {
            dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
        }
    }, [state.elements, state.template.name, dispatch, canvasWidth, canvasHeight, getTemplateIdFromUrl]);
    const previewTemplate = (0,react.useCallback)(() => {
        dispatch({ type: 'SET_SHOW_PREVIEW_MODAL', payload: true });
    }, [dispatch]);
    const newTemplate = (0,react.useCallback)(() => {
        dispatch({ type: 'NEW_TEMPLATE' });
    }, [dispatch]);
    const setTemplateModified = (0,react.useCallback)((modified) => {
        dispatch({ type: 'SET_TEMPLATE_MODIFIED', payload: modified });
    }, [dispatch]);
    const updateTemplateSettings = (0,react.useCallback)((settings) => {
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
        saveTemplate,
        previewTemplate,
        newTemplate,
        setTemplateModified,
        updateTemplateSettings
    };
}

;// ./assets/js/pdf-builder-react/constants/canvas.ts
/**
 * Constantes pour les dimensions du PDF Builder
 *
 * IMPORTANT: Le système utilise UNIQUEMENT les PIXELS (PX)
 * Les dimensions sont maintenant dynamiques selon les paramètres sauvegardés
 */
// Fonction pour récupérer les dimensions depuis les paramètres WordPress
const getCanvasDimensions = () => {
    var _a, _b;
    // Récupérer les dimensions depuis les paramètres sauvegardés
    const width = ((_a = window.pdfBuilderCanvasSettings) === null || _a === void 0 ? void 0 : _a.default_canvas_width) ||
        parseInt(localStorage.getItem('pdf_builder_canvas_width') || '794');
    const height = ((_b = window.pdfBuilderCanvasSettings) === null || _b === void 0 ? void 0 : _b.default_canvas_height) ||
        parseInt(localStorage.getItem('pdf_builder_canvas_height') || '1123');
    return {
        width: Math.max(100, Math.min(3000, width)),
        height: Math.max(100, Math.min(3000, height))
    };
};
// Dimensions par défaut pour le rendu canvas (récupérées dynamiquement)
const DEFAULT_CANVAS_WIDTH = getCanvasDimensions().width;
const DEFAULT_CANVAS_HEIGHT = getCanvasDimensions().height;
// Dimensions A4 en PIXELS (pour référence uniquement)
const CANVAS_DIMENSIONS = {
    A4_PORTRAIT: {
        width: 794,
        height: 1123,
        name: 'A4 Portrait'
    },
    A4_LANDSCAPE: {
        width: 1123,
        height: 794,
        name: 'A4 Landscape'
    }
};

;// ./assets/js/pdf-builder-react/utils/responsive.ts
/**
 * Utilitaires CSS pour le responsive design
 * Classes utilitaires pour une gestion simplifiée du responsive
 */

/**
 * Génère des classes CSS responsive
 */
const responsiveUtils = `
/* Utilitaires responsive automatiques */

/* Visibilité */
.hidden-xs { display: none !important; }
.hidden-sm { display: none !important; }
.hidden-md { display: none !important; }
.hidden-lg { display: none !important; }
.hidden-xl { display: none !important; }

.visible-xs { display: block !important; }
.visible-sm { display: block !important; }
.visible-md { display: block !important; }
.visible-lg { display: block !important; }
.visible-xl { display: block !important; }

/* Media queries pour la visibilité */
@media (min-width: ${BREAKPOINTS.xs}px) {
  .hidden-xs { display: inherit !important; }
  .visible-xs { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.sm}px) {
  .hidden-sm { display: inherit !important; }
  .visible-sm { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.md}px) {
  .hidden-md { display: inherit !important; }
  .visible-md { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.lg}px) {
  .hidden-lg { display: inherit !important; }
  .visible-lg { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.xl}px) {
  .hidden-xl { display: inherit !important; }
  .visible-xl { display: none !important; }
}

/* Flex utilities responsive */
.flex-column-xs { flex-direction: column !important; }
.flex-column-sm { flex-direction: column !important; }
.flex-column-md { flex-direction: column !important; }
.flex-row-xs { flex-direction: row !important; }
.flex-row-sm { flex-direction: row !important; }
.flex-row-md { flex-direction: row !important; }

/* Text alignment responsive */
.text-center-xs { text-align: center !important; }
.text-center-sm { text-align: center !important; }
.text-center-md { text-align: center !important; }
.text-left-xs { text-align: left !important; }
.text-left-sm { text-align: left !important; }
.text-left-md { text-align: left !important; }
.text-right-xs { text-align: right !important; }
.text-right-sm { text-align: right !important; }
.text-right-md { text-align: right !important; }

/* Spacing responsive */
.m-0-xs { margin: 0 !important; }
.m-0-sm { margin: 0 !important; }
.m-0-md { margin: 0 !important; }
.p-0-xs { padding: 0 !important; }
.p-0-sm { padding: 0 !important; }
.p-0-md { padding: 0 !important; }

/* Width responsive */
.w-100-xs { width: 100% !important; }
.w-100-sm { width: 100% !important; }
.w-100-md { width: 100% !important; }
.w-auto-xs { width: auto !important; }
.w-auto-sm { width: auto !important; }
.w-auto-md { width: auto !important; }

/* Container responsive */
.container-fluid-xs { width: 100% !important; padding-left: 15px !important; padding-right: 15px !important; }
.container-fluid-sm { width: 100% !important; padding-left: 15px !important; padding-right: 15px !important; }
.container-xs { max-width: 100% !important; margin: 0 auto !important; }
.container-sm { max-width: 100% !important; margin: 0 auto !important; }

/* Media queries pour les utilitaires */
@media (min-width: ${BREAKPOINTS.xs}px) {
  .flex-column-xs { flex-direction: row !important; }
  .flex-row-xs { flex-direction: column !important; }
  .text-center-xs { text-align: inherit !important; }
  .text-left-xs { text-align: inherit !important; }
  .text-right-xs { text-align: inherit !important; }
  .m-0-xs { margin: inherit !important; }
  .p-0-xs { padding: inherit !important; }
  .w-100-xs { width: inherit !important; }
  .w-auto-xs { width: inherit !important; }
  .container-fluid-xs { width: inherit !important; padding-left: inherit !important; padding-right: inherit !important; }
  .container-xs { max-width: inherit !important; }
}

@media (min-width: ${BREAKPOINTS.sm}px) {
  .flex-column-sm { flex-direction: row !important; }
  .flex-row-sm { flex-direction: column !important; }
  .text-center-sm { text-align: inherit !important; }
  .text-left-sm { text-align: inherit !important; }
  .text-right-sm { text-align: inherit !important; }
  .m-0-sm { margin: inherit !important; }
  .p-0-sm { padding: inherit !important; }
  .w-100-sm { width: inherit !important; }
  .w-auto-sm { width: inherit !important; }
  .container-fluid-sm { width: inherit !important; padding-left: inherit !important; padding-right: inherit !important; }
  .container-sm { max-width: inherit !important; }
}

@media (min-width: ${BREAKPOINTS.md}px) {
  .flex-column-md { flex-direction: row !important; }
  .flex-row-md { flex-direction: column !important; }
  .text-center-md { text-align: inherit !important; }
  .text-left-md { text-align: inherit !important; }
  .text-right-md { text-align: inherit !important; }
  .m-0-md { margin: inherit !important; }
  .p-0-md { padding: inherit !important; }
  .w-100-md { width: inherit !important; }
  .w-auto-md { width: inherit !important; }
}
`;
/**
 * Injecte les utilitaires CSS responsive dans le DOM
 */
function injectResponsiveUtils() {
    if (typeof document === 'undefined')
        return;
    const existingStyle = document.getElementById('pdf-builder-responsive-utils');
    if (existingStyle)
        return;
    const style = document.createElement('style');
    style.id = 'pdf-builder-responsive-utils';
    style.textContent = responsiveUtils;
    document.head.appendChild(style);
}

;// ./assets/js/pdf-builder-react/components/PDFBuilderContent.tsx













// ✅ Add spin animation
const spinStyles = `
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;
// Inject CSS
if (typeof document !== 'undefined') {
    const style = document.createElement('style');
    style.textContent = spinStyles;
    document.head.appendChild(style);
}
const PDFBuilderContent = (0,react.memo)(function PDFBuilderContent({ width = DEFAULT_CANVAS_WIDTH, height = DEFAULT_CANVAS_HEIGHT, className }) {
    debugLog('🏗️ PDFBuilderContent: Component initialized with props:', { width, height, className });
    const [isHeaderFixed, setIsHeaderFixed] = (0,react.useState)(false);
    const [isPropertiesPanelOpen, setIsPropertiesPanelOpen] = (0,react.useState)(false);
    const [manualSaveSuccess, setManualSaveSuccess] = (0,react.useState)(false);
    debugLog('📱 PDFBuilderContent: Initial state set:', { isHeaderFixed, isPropertiesPanelOpen, manualSaveSuccess });
    // Hooks responsives
    const isMobile = useResponsive_useIsMobile();
    const isTablet = useResponsive_useIsTablet();
    debugLog('📱 PDFBuilderContent: Responsive hooks:', { isMobile, isTablet });
    const { templateName, templateDescription, canvasWidth, canvasHeight, marginTop, marginBottom, showGuides, snapToGrid, isNewTemplate, isModified, isSaving, isLoading, // ✅ NEW: Template is loading
    isEditingExistingTemplate, saveTemplate, previewTemplate, newTemplate, updateTemplateSettings } = useTemplate();
    debugLog('📋 PDFBuilderContent: useTemplate hook values:', {
        templateName,
        templateDescription,
        canvasWidth,
        canvasHeight,
        marginTop,
        marginBottom,
        showGuides,
        snapToGrid,
        isNewTemplate,
        isModified,
        isSaving,
        isLoading,
        isEditingExistingTemplate
    });
    // Hook pour les paramètres du canvas
    const canvasSettings = CanvasSettingsContext_useCanvasSettings();
    debugLog('🎨 PDFBuilderContent: Canvas settings:', canvasSettings);
    // Injection des utilitaires responsives
    (0,react.useEffect)(() => {
        debugLog('🔧 PDFBuilderContent: Injecting responsive utils');
        injectResponsiveUtils();
        debugLog('✅ PDFBuilderContent: Responsive utils injected');
    }, []);
    // Effet pour gérer le scroll et ajuster le padding
    (0,react.useEffect)(() => {
        debugLog('📜 PDFBuilderContent: Setting up scroll handler');
        const handleScroll = () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const newIsHeaderFixed = scrollTop > 100;
            debugLog('📜 PDFBuilderContent: Scroll detected, scrollTop:', scrollTop, 'isHeaderFixed:', newIsHeaderFixed);
            setIsHeaderFixed(newIsHeaderFixed);
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        debugLog('✅ PDFBuilderContent: Scroll handler added');
        return () => {
            debugLog('🧹 PDFBuilderContent: Cleaning up scroll handler');
            window.removeEventListener('scroll', handleScroll);
        };
    }, []);
    // Wrapper pour sauvegarder
    const saveTemplateWithAutoSave = (0,react.useCallback)(async () => {
        debugLog('💾 PDFBuilderContent: Manual save initiated');
        try {
            // Effectuer la sauvegarde manuelle
            debugLog('🔄 PDFBuilderContent: Calling saveTemplate...');
            await saveTemplate();
            debugLog('✅ PDFBuilderContent: Manual save successful');
            debugLog('[PDF_BUILDER] Manual save successful');
            // Afficher une notification de succès
            if (typeof window !== 'undefined' && window.showSuccessNotification) {
                debugLog('🔔 PDFBuilderContent: Showing success notification');
                window.showSuccessNotification('Template sauvegardé avec succès !');
            }
        }
        catch (manualSaveError) {
            debug_debugError('❌ PDFBuilderContent: Manual save failed:', manualSaveError);
            debug_debugError('[PDF_BUILDER] Manual save failed:', manualSaveError);
            // Afficher une notification d'erreur
            if (typeof window !== 'undefined' && window.showErrorNotification) {
                debugLog('🔔 PDFBuilderContent: Showing error notification');
                window.showErrorNotification('Erreur lors de la sauvegarde du template');
            }
            throw manualSaveError; // Re-throw pour que l'UI montre l'erreur
        }
    }, [saveTemplate]);
    return ((0,jsx_runtime.jsx)(jsx_runtime.Fragment, { children: (0,jsx_runtime.jsxs)("div", { className: `pdf-builder ${className || ''}`, style: {
                display: 'flex',
                flexDirection: 'column',
                width: '100%',
                height: '100%',
                gap: '0px',
                padding: '0px',
                backgroundColor: '#ffffff',
                border: 'none',
                borderRadius: '0px',
                paddingTop: isHeaderFixed ? '132px' : '0px',
                transition: 'padding 0.3s ease'
            }, children: [(0,jsx_runtime.jsx)(Header, { templateName: templateName || '', templateDescription: templateDescription || '', canvasWidth: canvasWidth || 794, canvasHeight: canvasHeight || 1123, showGuides: showGuides || true, snapToGrid: snapToGrid || false, isNewTemplate: isNewTemplate, isModified: isModified, isSaving: isSaving, isLoading: isLoading, isEditingExistingTemplate: isEditingExistingTemplate, onSave: saveTemplateWithAutoSave, onPreview: previewTemplate, onNewTemplate: newTemplate, onUpdateTemplateSettings: updateTemplateSettings }), (0,jsx_runtime.jsx)("div", { style: { flexShrink: 0, padding: '12px 12px 0 12px' }, children: (0,jsx_runtime.jsx)(Toolbar, {}) }), (0,jsx_runtime.jsxs)("div", { style: { display: 'flex', flex: 1, gap: '0', padding: '12px' }, children: [(0,jsx_runtime.jsx)(ElementLibrary, {}), (0,jsx_runtime.jsxs)("div", { style: { flex: 1, display: 'flex', flexDirection: 'column', position: 'relative' }, children: [(0,jsx_runtime.jsxs)("div", { style: {
                                        flex: 1,
                                        display: 'flex',
                                        justifyContent: 'center',
                                        alignItems: 'center',
                                        backgroundColor: canvasSettings.containerBackgroundColor || '#f8f8f8',
                                        border: '1px solid #e0e0e0',
                                        borderRadius: '4px',
                                        overflow: 'visible',
                                        position: 'relative',
                                        paddingTop: '20px',
                                        paddingBottom: '20px'
                                    }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                position: 'absolute',
                                                top: '8px',
                                                right: '8px',
                                                backgroundColor: 'rgba(0, 122, 204, 0.9)',
                                                color: 'white',
                                                padding: '4px 8px',
                                                borderRadius: '4px',
                                                fontSize: '12px',
                                                fontWeight: 'bold',
                                                zIndex: 10
                                            }, children: (() => {
                                                var _a, _b, _c;
                                                const format = ((_a = window.pdfBuilderCanvasSettings) === null || _a === void 0 ? void 0 : _a.default_canvas_format) || 'A4';
                                                const dpi = ((_b = window.pdfBuilderCanvasSettings) === null || _b === void 0 ? void 0 : _b.default_canvas_dpi) || 96;
                                                const orientation = ((_c = window.pdfBuilderCanvasSettings) === null || _c === void 0 ? void 0 : _c.default_canvas_orientation) || 'portrait';
                                                const paperFormats = window.pdfBuilderPaperFormats || {
                                                    'A4': { width: 210, height: 297 },
                                                    'A3': { width: 297, height: 420 },
                                                    'A5': { width: 148, height: 210 },
                                                    'Letter': { width: 215.9, height: 279.4 },
                                                    'Legal': { width: 215.9, height: 355.6 },
                                                    'Tabloid': { width: 279.4, height: 431.8 }
                                                };
                                                // Récupérer les dimensions en mm
                                                const dimsMM = paperFormats[format] || paperFormats['A4'];
                                                // Calculer les dimensions en pixels avec le DPI actuel
                                                const pixelsPerMM = dpi / 25.4;
                                                let widthPx = Math.round(dimsMM.width * pixelsPerMM);
                                                let heightPx = Math.round(dimsMM.height * pixelsPerMM);
                                                // Inverser si orientation paysage
                                                if (orientation === 'landscape') {
                                                    [widthPx, heightPx] = [heightPx, widthPx];
                                                }
                                                return `${format}: ${widthPx}×${heightPx}px (${dpi} DPI)`;
                                            })() }), isLoading && ((0,jsx_runtime.jsx)("div", { style: {
                                                position: 'absolute',
                                                top: 0,
                                                left: 0,
                                                right: 0,
                                                bottom: 0,
                                                backgroundColor: 'rgba(255, 255, 255, 0.7)',
                                                display: 'flex',
                                                justifyContent: 'center',
                                                alignItems: 'center',
                                                zIndex: 100,
                                                borderRadius: '4px'
                                            }, children: (0,jsx_runtime.jsxs)("div", { style: { textAlign: 'center' }, children: [(0,jsx_runtime.jsx)("div", { style: {
                                                            width: '40px',
                                                            height: '40px',
                                                            border: '4px solid #e0e0e0',
                                                            borderTop: '4px solid #007acc',
                                                            borderRadius: '50%',
                                                            animation: 'spin 1s linear infinite',
                                                            margin: '0 auto 12px'
                                                        } }), (0,jsx_runtime.jsx)("p", { style: { margin: 0, color: '#666', fontSize: '14px' }, children: "Chargement du template..." })] }) })), !isLoading && ((0,jsx_runtime.jsxs)(jsx_runtime.Fragment, { children: [debugLog('🎨 PDFBuilderContent: Rendering Canvas component'), (0,jsx_runtime.jsx)(Canvas, { width: width, height: height })] }))] }), (0,jsx_runtime.jsx)("button", { onClick: () => {
                                        debugLog('🔘 PDFBuilderContent: Properties panel toggle clicked, current state:', isPropertiesPanelOpen);
                                        setIsPropertiesPanelOpen(!isPropertiesPanelOpen);
                                        debugLog('🔄 PDFBuilderContent: Properties panel state changed to:', !isPropertiesPanelOpen);
                                    }, style: {
                                        position: 'absolute',
                                        top: '50%',
                                        right: isPropertiesPanelOpen ? '-12px' : '0px',
                                        transform: 'translateY(-50%)',
                                        zIndex: 20,
                                        padding: '8px 6px',
                                        backgroundColor: '#007acc',
                                        color: 'white',
                                        border: 'none',
                                        borderRadius: '4px 0 0 4px',
                                        cursor: 'pointer',
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        boxShadow: '0 2px 4px rgba(0,0,0,0.2)',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        width: '24px',
                                        height: '60px',
                                        writingMode: 'vertical-rl',
                                        textOrientation: 'mixed'
                                    }, title: isPropertiesPanelOpen ? 'Fermer le panneau de propriétés' : 'Ouvrir le panneau de propriétés', children: isPropertiesPanelOpen ? '▷' : '◁' })] }), isPropertiesPanelOpen && ((0,jsx_runtime.jsx)("div", { style: {
                                flexShrink: 0,
                                width: '430px',
                                position: 'sticky',
                                top: '110px',
                                height: 'fit-content',
                                maxHeight: 'calc(100vh - 32px)'
                            }, children: (0,jsx_runtime.jsx)(PropertiesPanel, {}) }))] })] }) }));
});

;// ./assets/js/pdf-builder-react/PDFBuilder.tsx







debugLog('🔧 [PDFBuilder.tsx] Import successful. React:', typeof react, 'useState:', typeof react.useState, 'useEffect:', typeof react.useEffect);
debugLog('🔧 [PDFBuilder.tsx] window.pdfBuilderData at import time:', window.pdfBuilderData);
debugLog('🔧 [PDFBuilder.tsx] window keys at import time:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
function PDFBuilder({ width: initialWidth = DEFAULT_CANVAS_WIDTH, height: initialHeight = DEFAULT_CANVAS_HEIGHT, className }) {
    var _a, _b;
    debugLog('🔧 PDFBuilder: Component initialized with props:', { initialWidth, initialHeight, className });
    debugLog('🔧 PDFBuilder: window.pdfBuilderData at component init:', window.pdfBuilderData);
    debugLog('🔧 PDFBuilder: window.pdfBuilderData?.ajaxUrl:', (_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.ajaxUrl);
    debugLog('🔧 PDFBuilder: window.pdfBuilderData?.nonce:', (_b = window.pdfBuilderData) === null || _b === void 0 ? void 0 : _b.nonce);
    const [dimensions, setDimensions] = (0,react.useState)({
        width: initialWidth,
        height: initialHeight
    });
    debugLog('📏 PDFBuilder: Initial dimensions set:', dimensions);
    // Écouter les changements de dimensions depuis l'API globale
    (0,react.useEffect)(() => {
        debugLog('🎧 PDFBuilder: Setting up dimension change listener');
        const handleUpdateDimensions = (event) => {
            debugLog('📡 PDFBuilder: Received dimension update event:', event.detail);
            const { width, height } = event.detail;
            debugLog('🔄 PDFBuilder: Updating dimensions to:', { width, height });
            setDimensions({ width, height });
        };
        document.addEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions, { passive: true });
        debugLog('✅ PDFBuilder: Dimension change listener added');
        return () => {
            debugLog('🧹 PDFBuilder: Cleaning up dimension change listener');
            document.removeEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions);
        };
    }, []);
    debugLog('🎨 PDFBuilder: Rendering with dimensions:', dimensions);
    return ((0,jsx_runtime.jsx)(CanvasSettingsProvider, { children: (0,jsx_runtime.jsx)(BuilderProvider, { children: (0,jsx_runtime.jsx)(PDFBuilderContent, { width: dimensions.width, height: dimensions.height, className: className }) }) }));
}
// Export des composants individuels pour une utilisation modulaire



/***/ })

}]);
//# sourceMappingURL=271.a73218e5624127c3d487.js.map