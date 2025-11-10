import { useMemo } from 'react';

/**
 * Hook pour accéder aux paramètres du canvas
 * Retourne tous les paramètres canvas depuis les options WordPress
 *
 * @returns {Object} Les paramètres du canvas
 */
export const useCanvasSettings = () => {
    const settings = useMemo(() => {
        // Récupérer les paramètres depuis window.pdfBuilderCanvasSettings
        // définis par Canvas_Manager::get_canvas_settings_script()
        if (typeof window !== 'undefined' && window.pdfBuilderCanvasSettings) {
            return window.pdfBuilderCanvasSettings;
        }
        return getDefaultCanvasSettings();
    }, []);

    return settings;
};

/**
 * Hook pour accéder à un paramètre canvas spécifique
 * 
 * @param {string} key - Clé du paramètre
 * @param {unknown} defaultValue - Valeur par défaut
 * @returns {unknown} La valeur du paramètre
 */
export const useCanvasSetting = (key: string, defaultValue: unknown = null) => {
    const settings = useCanvasSettings() as Record<string, unknown>;
    return useMemo(() => {
        return key in settings ? settings[key] : defaultValue;
    }, [key, settings, defaultValue]);
};

/**
 * Hook pour accéder aux dimensions du canvas
 * 
 * @returns {Object} Les dimensions (width, height, unit, orientation)
 */
export const useCanvasDimensions = () => {
    const settings = useCanvasSettings();
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
export const useCanvasMargins = () => {
    const settings = useCanvasSettings();
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
export const useGridSettings = () => {
    const settings = useCanvasSettings();
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
export const useZoomSettings = () => {
    const settings = useCanvasSettings();
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
export const useSelectionSettings = () => {
    const settings = useCanvasSettings();
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
export const useExportSettings = () => {
    const settings = useCanvasSettings();
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
export const useHistorySettings = () => {
    const settings = useCanvasSettings();
    return useMemo(() => ({
        undoLevels: settings.undo_levels || 50,
        redoLevels: settings.redo_levels || 50,
        autoSave: settings.auto_save_enabled || true,
        autoSaveInterval: settings.auto_save_interval || 30,
        autoSaveVersions: settings.auto_save_versions || 10,
    }), [settings]);
};

/**
 * Retourne les paramètres par défaut du canvas
 * 
 * @returns {Object} Paramètres par défaut
 */
export const getDefaultCanvasSettings = () => ({
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
    auto_save_interval: 30,
    auto_save_versions: 10,
    undo_levels: 50,
    redo_levels: 50,
    enable_keyboard_shortcuts: true,
    debug_mode: false,
    show_fps: false,
});
