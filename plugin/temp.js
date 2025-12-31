(function($) {
'use strict';

console.log('[PDF Builder] temp.js loaded successfully');

// Valeurs par défaut globales pour tous les champs Canvas - SOURCE UNIQUE DE VÉRITÉ
const CANVAS_DEFAULT_VALUES = {
    'pdf_builder_canvas_width': '794',
    'pdf_builder_canvas_height': '1123',
    'pdf_builder_canvas_dpi': '96',
    'pdf_builder_canvas_format': 'A4',
    'pdf_builder_canvas_bg_color': '#ffffff',
    'pdf_builder_canvas_border_color': '#cccccc',
    'pdf_builder_canvas_border_width': '1',
    'pdf_builder_canvas_container_bg_color': '#f8f9fa',
    'pdf_builder_canvas_shadow_enabled': '0',
    'pdf_builder_canvas_grid_enabled': '1',
    'pdf_builder_canvas_grid_size': '20',
    'pdf_builder_canvas_guides_enabled': '1',
    'pdf_builder_canvas_snap_to_grid': '1',
    'pdf_builder_canvas_zoom_min': '25',
    'pdf_builder_canvas_zoom_max': '500',
    'pdf_builder_canvas_zoom_default': '100',
    'pdf_builder_canvas_zoom_step': '25',
    'pdf_builder_canvas_export_quality': '90',
    'pdf_builder_canvas_export_format': 'png',
    'pdf_builder_canvas_export_transparent': '0',
    'pdf_builder_canvas_drag_enabled': '1',
    'pdf_builder_canvas_resize_enabled': '1',
    'pdf_builder_canvas_rotate_enabled': '1',
    'pdf_builder_canvas_multi_select': '1',
    'pdf_builder_canvas_selection_mode': 'single',
    'pdf_builder_canvas_keyboard_shortcuts': '1',
    'pdf_builder_canvas_fps_target': '60',
    'pdf_builder_canvas_memory_limit_js': '50',
    'pdf_builder_canvas_response_timeout': '5000',
    'pdf_builder_canvas_lazy_loading_editor': '1',
    'pdf_builder_canvas_preload_critical': '1',
    'pdf_builder_canvas_lazy_loading_plugin': '1',
    'pdf_builder_canvas_debug_enabled': '0',
    'pdf_builder_canvas_performance_monitoring': '0',
    'pdf_builder_canvas_error_reporting': '0',
    'pdf_builder_canvas_memory_limit_php': '128'
};

// Configuration simplifiée - SAUVEGARDE DIRECTE EN BASE UNIQUEMENT
const CANVAS_CONFIG = {
    MAX_RETRIES: 3,
    RETRY_DELAY: 1000, // ms
    AJAX_TIMEOUT: 30000 // 30 secondes
};

// Feature flags simplifiés
const CANVAS_FEATURES = {
    ENABLE_VALIDATION: true,
    ENABLE_DEBUG: false,
    ENABLE_PERFORMANCE_MONITORING: false
};

// Exposer les constantes globalement si nécessaire
window.CANVAS_DEFAULT_VALUES = CANVAS_DEFAULT_VALUES;
window.CANVAS_CONFIG = CANVAS_CONFIG;
window.CANVAS_FEATURES = CANVAS_FEATURES;

console.log('[PDF Builder] Canvas constants initialized:', {
    defaults: Object.keys(CANVAS_DEFAULT_VALUES).length,
    config: CANVAS_CONFIG,
    features: CANVAS_FEATURES
});

})(jQuery);