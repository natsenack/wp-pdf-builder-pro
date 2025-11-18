<?php
/**
 * Paramètres canvas pour JavaScript
 * Définit les paramètres canvas globalement avant le chargement du JS
 */

// Récupérer les paramètres canvas depuis les options WordPress
$canvas_settings_js = get_option('pdf_builder_canvas_settings', []);

// Définir pdfBuilderCanvasSettings globalement avant tout autre script
?>
<script>
window.pdfBuilderCanvasSettings = <?php echo wp_json_encode([
    'default_canvas_format' => $canvas_settings_js['default_canvas_format'] ?? 'A4',
    'default_canvas_orientation' => $canvas_settings_js['default_canvas_orientation'] ?? 'portrait',
    'default_canvas_unit' => $canvas_settings_js['default_canvas_unit'] ?? 'px',
    'default_orientation' => $canvas_settings_js['default_orientation'] ?? 'portrait',
    'canvas_background_color' => $canvas_settings_js['canvas_background_color'] ?? '#ffffff',
    'canvas_show_transparency' => $canvas_settings_js['canvas_show_transparency'] ?? false,
    'container_background_color' => $canvas_settings_js['container_background_color'] ?? '#f8f9fa',
    'container_show_transparency' => $canvas_settings_js['container_show_transparency'] ?? false,
    'margin_top' => $canvas_settings_js['margin_top'] ?? 28,
    'margin_right' => $canvas_settings_js['margin_right'] ?? 28,
    'margin_bottom' => $canvas_settings_js['margin_bottom'] ?? 10,
    'margin_left' => $canvas_settings_js['margin_left'] ?? 10,
    'show_margins' => ($canvas_settings_js['show_margins'] ?? false) === '1',
    'show_grid' => ($canvas_settings_js['show_grid'] ?? false) === '1',
    'grid_size' => $canvas_settings_js['grid_size'] ?? 10,
    'grid_color' => $canvas_settings_js['grid_color'] ?? '#e0e0e0',
    'snap_to_elements' => ($canvas_settings_js['snap_to_elements'] ?? false) === '1',
    'snap_tolerance' => $canvas_settings_js['snap_tolerance'] ?? 5,
    'show_guides' => ($canvas_settings_js['show_guides'] ?? false) === '1',
    'default_zoom' => $canvas_settings_js['default_zoom'] ?? 100,
    'zoom_step' => $canvas_settings_js['zoom_step'] ?? 25,
    'min_zoom' => $canvas_settings_js['min_zoom'] ?? 10,
    'max_zoom' => $canvas_settings_js['max_zoom'] ?? 500,
    'zoom_with_wheel' => ($canvas_settings_js['zoom_with_wheel'] ?? false) === '1',
    'pan_with_mouse' => ($canvas_settings_js['pan_with_mouse'] ?? false) === '1',
    'show_resize_handles' => ($canvas_settings_js['show_resize_handles'] ?? false) === '1',
    'handle_size' => $canvas_settings_js['handle_size'] ?? 8,
    'handle_color' => $canvas_settings_js['handle_color'] ?? '#007cba',
    'enable_rotation' => ($canvas_settings_js['enable_rotation'] ?? false) === '1',
    'rotation_step' => $canvas_settings_js['rotation_step'] ?? 15,
    'multi_select' => $canvas_settings_js['multi_select'] ?? false,
    'copy_paste_enabled' => $canvas_settings_js['copy_paste_enabled'] ?? false,
    'export_quality' => $canvas_settings_js['export_quality'] ?? 'print',
    'export_format' => $canvas_settings_js['export_format'] ?? 'pdf',
    'compress_images' => $canvas_settings_js['compress_images'] ?? true,
    'image_quality' => $canvas_settings_js['image_quality'] ?? 85,
    'max_image_size' => $canvas_settings_js['max_image_size'] ?? 2048,
    'include_metadata' => $canvas_settings_js['include_metadata'] ?? true,
    'pdf_author' => $canvas_settings_js['pdf_author'] ?? 'PDF Builder Pro',
    'pdf_subject' => $canvas_settings_js['pdf_subject'] ?? '',
    'auto_crop' => $canvas_settings_js['auto_crop'] ?? false,
    'embed_fonts' => $canvas_settings_js['embed_fonts'] ?? true,
    'optimize_for_web' => $canvas_settings_js['optimize_for_web'] ?? true,
    'enable_hardware_acceleration' => $canvas_settings_js['enable_hardware_acceleration'] ?? true,
    'limit_fps' => $canvas_settings_js['limit_fps'] ?? true,
    'max_fps' => $canvas_settings_js['max_fps'] ?? 60,
    'auto_save_enabled' => $canvas_settings_js['auto_save_enabled'] ?? false,
    'auto_save_interval' => $canvas_settings_js['auto_save_interval'] ?? 30,
    'auto_save_versions' => $canvas_settings_js['auto_save_versions'] ?? 10,
    'undo_levels' => $canvas_settings_js['undo_levels'] ?? 50,
    'redo_levels' => $canvas_settings_js['redo_levels'] ?? 50,
    'enable_keyboard_shortcuts' => $canvas_settings_js['enable_keyboard_shortcuts'] ?? true,
    'debug_mode' => $canvas_settings_js['debug_mode'] ?? false,
    'show_fps' => $canvas_settings_js['show_fps'] ?? false
]); ?>;

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