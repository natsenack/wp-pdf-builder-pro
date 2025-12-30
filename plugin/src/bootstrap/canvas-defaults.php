<?php
/**
 * PDF Builder Pro - Canvas Defaults Module
 * Initialisation des paramètres canvas par défaut
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// INITIALISER LES PARAMÈTRES CANVAS PAR DÉFAUT
// ============================================================================

/**
 * Initialise les paramètres canvas avec leurs valeurs par défaut
 * Cette fonction ne fait rien si les paramètres existent déjà
 */
function pdf_builder_initialize_canvas_defaults() {
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $canvas_defaults = [
        // Dimensions & Format
        'pdf_builder_canvas_format' => 'A4',
        'pdf_builder_canvas_orientation' => 'portrait',
        'pdf_builder_canvas_unit' => 'px',
        'pdf_builder_canvas_dpi' => 96,
        'pdf_builder_canvas_width' => 794,
        'pdf_builder_canvas_height' => 1123,

        // Apparence
        'pdf_builder_canvas_bg_color' => '#ffffff',
        'pdf_builder_canvas_border_color' => '#cccccc',
        'pdf_builder_canvas_border_width' => 1,
        'pdf_builder_canvas_shadow_enabled' => '0',
        'pdf_builder_canvas_container_bg_color' => '#f8f9fa',

        // Zoom & Navigation
        'pdf_builder_canvas_zoom_min' => 10,
        'pdf_builder_canvas_zoom_max' => 500,
        'pdf_builder_canvas_zoom_default' => 100,
        'pdf_builder_canvas_zoom_step' => 25,

        // Grille
        'pdf_builder_canvas_grid_enabled' => '1',
        'pdf_builder_canvas_grid_size' => 20,
        'pdf_builder_canvas_snap_to_grid' => '1',
        'pdf_builder_canvas_guides_enabled' => '1',

        // Interactions
        'pdf_builder_canvas_drag_enabled' => '1',
        'pdf_builder_canvas_resize_enabled' => '1',
        'pdf_builder_canvas_rotate_enabled' => '1',
        'pdf_builder_canvas_multi_select' => '1',
        'pdf_builder_canvas_keyboard_shortcuts' => '1',
        'pdf_builder_canvas_selection_mode' => 'bounding_box',

        // Export
        'pdf_builder_canvas_export_format' => 'png',
        'pdf_builder_canvas_export_quality' => 90,
        'pdf_builder_canvas_export_transparent' => '0',

        // Performance
        'pdf_builder_canvas_fps_target' => 60,
        'pdf_builder_canvas_memory_limit_js' => 128,
        'pdf_builder_canvas_memory_limit_php' => 256,

        // Debug
        'pdf_builder_canvas_debug_enabled' => '0',
        'pdf_builder_canvas_performance_monitoring' => '0',
        'pdf_builder_canvas_error_reporting' => '0',
    ];

    foreach ($canvas_defaults as $option_name => $default_value) {
        if (!get_option($option_name)) {
            update_option($option_name, $default_value);
        }
    }

    $initialized = true;
}

// Initialiser les paramètres canvas par défaut
pdf_builder_initialize_canvas_defaults();