// Force cache clear
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for(let registration of registrations) {
            registration.unregister();
        }
    });
}
console.log('Cache cleared by PDF Builder');

// Gestionnaire des modales Canvas
(function() {
    'use strict';

    // Valeurs par défaut pour les paramètres Canvas (injectées depuis PHP)
    const CANVAS_DEFAULT_VALUES = {
        "pdf_builder_canvas_width": "794",
        "pdf_builder_canvas_height": "1123",
        "pdf_builder_canvas_dpi": "96",
        "pdf_builder_canvas_format": "A4",
        "pdf_builder_canvas_bg_color": "#ffffff",
        "pdf_builder_canvas_border_color": "#cccccc",
        "pdf_builder_canvas_border_width": "1",
        "pdf_builder_canvas_container_bg_color": "#f8f9fa",
        "pdf_builder_canvas_shadow_enabled": "0",
        "pdf_builder_canvas_grid_enabled": "1",
        "pdf_builder_canvas_grid_size": "20",
        "pdf_builder_canvas_guides_enabled": "1",
        "pdf_builder_canvas_snap_to_grid": "1",
        "pdf_builder_canvas_zoom_min": "25",
        "pdf_builder_canvas_zoom_max": "500",
        "pdf_builder_canvas_zoom_default": "100",
        "pdf_builder_canvas_zoom_step": "25",
        "pdf_builder_canvas_export_quality": "90",
        "pdf_builder_canvas_export_format": "png",
        "pdf_builder_canvas_export_transparent": "0",
        "pdf_builder_canvas_drag_enabled": "1",
        "pdf_builder_canvas_resize_enabled": "1",
        "pdf_builder_canvas_rotate_enabled": "1",
        "pdf_builder_canvas_multi_select": "1",
        "pdf_builder_canvas_selection_mode": "single",
        "pdf_builder_canvas_keyboard_shortcuts": "1",
        "pdf_builder_canvas_fps_target": "60",
        "pdf_builder_canvas_memory_limit_js": "50",
        "pdf_builder_canvas_response_timeout": "5000",
        "pdf_builder_canvas_lazy_loading_editor": "1",
        "pdf_builder_canvas_preload_critical": "1",
        "pdf_builder_canvas_lazy_loading_plugin": "1",
        "pdf_builder_canvas_debug_enabled": "0",
        "pdf_builder_canvas_performance_monitoring": "0",
        "pdf_builder_canvas_error_reporting": "0",
        "pdf_builder_canvas_memory_limit_php": "256"
    };

    console.log('[PDF Builder] 🚀 MODALS_SYSTEM_v2.1 - Initializing Canvas modals system (FIXED VERSION)');
    console.log('[PDF Builder] 📅 Date: 2025-12-11 21:35');
    console.log('[PDF Builder] 🔧 Fix: HTML/PHP moved outside script tags');

    // Fonction d'initialisation avec retry
    function initializeModals(retryCount = 0) {
        const maxRetries = 5;
        const retryDelay = 200; // ms

        try {
            console.log(`[PDF Builder] MODALS_INIT - Initializing Canvas modals system (attempt ${retryCount + 1}/${maxRetries + 1})`);

            // Initialiser l'état des toggles existants
            initializeToggleStates();</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\test-js-syntax.js