<?php
/**
 * Paramètres canvas pour JavaScript
 * Définit les paramètres canvas globalement avant le chargement du JS
 */

// Récupérer les paramètres canvas depuis les options séparées (synchronisées)
$canvas_settings_js = [
    'default_canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
    'default_canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
    'default_canvas_unit' => get_option('pdf_builder_canvas_unit', 'px'),
    'default_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
    'canvas_background_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
    'canvas_show_transparency' => get_option('pdf_builder_canvas_show_transparency', false),
    'container_background_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
    'container_show_transparency' => get_option('pdf_builder_canvas_container_show_transparency', false),
    'border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
    'border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
    'shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0') == '1',
    'margin_top' => intval(get_option('pdf_builder_canvas_margin_top', 28)),
    'margin_right' => intval(get_option('pdf_builder_canvas_margin_right', 28)),
    'margin_bottom' => intval(get_option('pdf_builder_canvas_margin_bottom', 10)),
    'margin_left' => intval(get_option('pdf_builder_canvas_margin_left', 10)),
    'show_margins' => get_option('pdf_builder_canvas_show_margins', false) == '1',
    'show_grid' => get_option('pdf_builder_canvas_grid_enabled', true) == '1',
    'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
    'grid_color' => get_option('pdf_builder_canvas_grid_color', '#e0e0e0'),
    'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', true) == '1',
    'snap_to_elements' => get_option('pdf_builder_canvas_snap_to_elements', false) == '1',
    'snap_tolerance' => intval(get_option('pdf_builder_canvas_snap_tolerance', 5)),
    'show_guides' => get_option('pdf_builder_canvas_guides_enabled', true) == '1',
    
    // 🔍 Zoom & Navigation
    'navigation_enabled' => get_option('pdf_builder_canvas_navigation_enabled', true) == '1',
    'default_zoom' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
    'min_zoom' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
    'max_zoom' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
    'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
    'zoom_with_wheel' => get_option('pdf_builder_canvas_zoom_with_wheel', true) == '1',
    'pan_with_mouse' => get_option('pdf_builder_canvas_pan_enabled', true) == '1',
    
    'show_resize_handles' => get_option('pdf_builder_canvas_show_resize_handles', true) == '1',
    'handle_size' => intval(get_option('pdf_builder_canvas_handle_size', 8)),
    'handle_color' => get_option('pdf_builder_canvas_handle_color', '#007cba'),
    'enable_rotation' => get_option('pdf_builder_canvas_rotate_enabled', true) == '1',
    'rotation_step' => intval(get_option('pdf_builder_canvas_rotation_step', 15)),
    'multi_select' => get_option('pdf_builder_canvas_multi_select', true) == '1',
    'copy_paste_enabled' => get_option('pdf_builder_canvas_copy_paste_enabled', true) == '1',
    'export_quality' => get_option('pdf_builder_canvas_export_quality', 90),
    'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
    'compress_images' => get_option('pdf_builder_canvas_compress_images', true) == '1',
    'image_quality' => intval(get_option('pdf_builder_canvas_image_quality', 85)),
    'max_image_size' => intval(get_option('pdf_builder_canvas_max_image_size', 2048)),
    'include_metadata' => get_option('pdf_builder_canvas_include_metadata', true) == '1',
    'pdf_author' => get_option('pdf_builder_canvas_pdf_author', 'PDF Builder Pro'),
    'pdf_subject' => get_option('pdf_builder_canvas_pdf_subject', ''),
    'auto_crop' => get_option('pdf_builder_canvas_auto_crop', false) == '1',
    'embed_fonts' => get_option('pdf_builder_canvas_embed_fonts', true) == '1',
    'optimize_for_web' => get_option('pdf_builder_canvas_optimize_for_web', true) == '1',
    'enable_hardware_acceleration' => get_option('pdf_builder_canvas_enable_hardware_acceleration', true) == '1',
    'limit_fps' => get_option('pdf_builder_canvas_limit_fps', true) == '1',
    'max_fps' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
    'auto_save_enabled' => get_option('pdf_builder_canvas_auto_save', true) == '1',
    'auto_save_interval' => intval(get_option('pdf_builder_canvas_auto_save_interval', 30)),
    'auto_save_versions' => intval(get_option('pdf_builder_canvas_auto_save_versions', 10)),
    'undo_levels' => intval(get_option('pdf_builder_canvas_undo_levels', 50)),
    'redo_levels' => intval(get_option('pdf_builder_canvas_redo_levels', 50)),
    'enable_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', true) == '1',
    'debug_mode' => get_option('pdf_builder_canvas_debug_mode', false) == '1',
    'show_fps' => get_option('pdf_builder_canvas_show_fps', false) == '1'
];

// Définir pdfBuilderCanvasSettings globalement avant tout autre script
?>
<script>
window.pdfBuilderCanvasSettings = <?php echo wp_json_encode($canvas_settings_js); ?>;

// Fonction pour convertir le format et l'orientation en dimensions pixels
window.pdfBuilderCanvasSettings.getDimensionsFromFormat = function(format, orientation) {
    const formatDimensions = {
        'A6': { width: 349, height: 496 },
        'A5': { width: 496, height: 701 },
        'A4': { width: 794, height: 1123 },
        'A3': { width: 1123, height: 1587 },
        'A2': { width: 1587, height: 2245 },
        'A1': { width: 2245, height: 3175 },
        'A0': { width: 3175, height: 4494 },
        'Letter': { width: 816, height: 1056 },
        'Legal': { width: 816, height: 1344 },
        'Tabloid': { width: 1056, height: 1632 }
    };

    const dims = formatDimensions[format] || formatDimensions['A4'];

    // Inverser les dimensions si orientation paysage
    if (orientation === 'landscape') {
        return { width: dims.height, height: dims.width };
    }

    return dims;
};

// Ajouter les dimensions calculées aux paramètres
window.pdfBuilderCanvasSettings.default_canvas_width = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
    window.pdfBuilderCanvasSettings.default_canvas_format,
    window.pdfBuilderCanvasSettings.default_canvas_orientation
).width;

window.pdfBuilderCanvasSettings.default_canvas_height = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
    window.pdfBuilderCanvasSettings.default_canvas_format,
    window.pdfBuilderCanvasSettings.default_canvas_orientation
).height;

// ✅ PDF_BUILDER_VERBOSE initialized in PDF_Builder_Admin.php via wp_add_inline_script()
</script>