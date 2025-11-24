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
    'default_canvas_dpi' => intval(get_option('pdf_builder_canvas_dpi', 96)),
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
    'show_margins' => get_option('pdf_builder_canvas_show_margins', '0') == '1',
    'show_grid' => get_option('pdf_builder_canvas_grid_enabled', '1') == '1',
    'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
    'grid_color' => get_option('pdf_builder_canvas_grid_color', '#e0e0e0'),
    'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') == '1',
    'snap_to_elements' => get_option('pdf_builder_canvas_snap_to_elements', '0') == '1',
    'snap_tolerance' => intval(get_option('pdf_builder_canvas_snap_tolerance', 5)),
    'show_guides' => get_option('pdf_builder_canvas_guides_enabled', '1') == '1',
    
    // 🔍 Zoom & Navigation
    'navigation_enabled' => get_option('pdf_builder_canvas_navigation_enabled', '1') == '1',
    'default_zoom' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
    'min_zoom' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
    'max_zoom' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
    'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
    'zoom_with_wheel' => get_option('pdf_builder_canvas_zoom_with_wheel', '1') == '1',
    'pan_with_mouse' => get_option('pdf_builder_canvas_pan_enabled', '1') == '1',
    
    'show_resize_handles' => get_option('pdf_builder_canvas_show_resize_handles', '1') == '1',
    'handle_size' => intval(get_option('pdf_builder_canvas_handle_size', 8)),
    'handle_color' => get_option('pdf_builder_canvas_handle_color', '#007cba'),
    'enable_rotation' => get_option('pdf_builder_canvas_rotate_enabled', '1') == '1',
    'rotation_step' => intval(get_option('pdf_builder_canvas_rotation_step', 15)),
    'multi_select' => get_option('pdf_builder_canvas_multi_select', '1') == '1',
    'copy_paste_enabled' => get_option('pdf_builder_canvas_copy_paste_enabled', '1') == '1',
    
    // Interactions - paramètres manquants
    'drag_enabled' => get_option('pdf_builder_canvas_drag_enabled', '1') == '1',
    'resize_enabled' => get_option('pdf_builder_canvas_resize_enabled', '1') == '1',
    'selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'rectangle'),
    'export_quality' => get_option('pdf_builder_canvas_export_quality', 90),
    'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
    'compress_images' => get_option('pdf_builder_canvas_compress_images', '1') == '1',
    'image_quality' => intval(get_option('pdf_builder_canvas_image_quality', 85)),
    'max_image_size' => intval(get_option('pdf_builder_canvas_max_image_size', 2048)),
    'include_metadata' => get_option('pdf_builder_canvas_include_metadata', '1') == '1',
    'pdf_author' => get_option('pdf_builder_canvas_pdf_author', 'PDF Builder Pro'),
    'pdf_subject' => get_option('pdf_builder_canvas_pdf_subject', ''),
    'auto_crop' => get_option('pdf_builder_canvas_auto_crop', '0') == '1',
    'embed_fonts' => get_option('pdf_builder_canvas_embed_fonts', '1') == '1',
    'optimize_for_web' => get_option('pdf_builder_canvas_optimize_for_web', '1') == '1',
    'enable_hardware_acceleration' => get_option('pdf_builder_canvas_enable_hardware_acceleration', '1') == '1',
    'limit_fps' => get_option('pdf_builder_canvas_limit_fps', '1') == '1',
    'max_fps' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
    
    // Performance - paramètres manquants
    'fps_target' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
    'memory_limit_js' => intval(get_option('pdf_builder_canvas_memory_limit_js', 128)),
    'memory_limit_php' => intval(get_option('pdf_builder_canvas_memory_limit_php', 256)),
    'lazy_loading_editor' => get_option('pdf_builder_canvas_lazy_loading_editor', '1') == '1',
    'lazy_loading_plugin' => get_option('pdf_builder_canvas_lazy_loading_plugin', '1') == '1',
    
    'auto_save_enabled' => get_option('pdf_builder_canvas_autosave_enabled', '1') == '1',
    'auto_save_interval' => intval(get_option('pdf_builder_canvas_auto_save_interval', 300)),
    'auto_save_versions' => intval(get_option('pdf_builder_canvas_auto_save_versions', 10)),
    
    // Autosave - paramètres avec noms cohérents
    'autosave_enabled' => get_option('pdf_builder_canvas_autosave_enabled', '1') == '1',
    'autosave_interval' => intval(get_option('pdf_builder_canvas_auto_save_interval', 300)),
    'versions_limit' => intval(get_option('pdf_builder_canvas_auto_save_versions', 10)),
    'undo_levels' => intval(get_option('pdf_builder_canvas_undo_levels', 50)),
    'redo_levels' => intval(get_option('pdf_builder_canvas_redo_levels', 50)),
    'enable_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') == '1',
    'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
    'debug_mode' => get_option('pdf_builder_canvas_debug_mode', '0') == '1',
    'show_fps' => get_option('pdf_builder_canvas_show_fps', '0') == '1'
];

// Définir pdfBuilderCanvasSettings globalement avant tout autre script
?>
<script>
window.pdfBuilderCanvasSettings = <?php echo wp_json_encode($canvas_settings_js); ?>;

// Dimensions standard des formats de papier en mm (centralisées)
window.pdfBuilderPaperFormats = <?php echo wp_json_encode(\PDF_Builder\PAPER_FORMATS); ?>;

// Fonction pour convertir le format et l'orientation en dimensions pixels
window.pdfBuilderCanvasSettings.getDimensionsFromFormat = function(format, orientation) {
    // Utiliser les dimensions standard centralisées en mm
    const formatDimensionsMM = window.pdfBuilderPaperFormats || {
        'A4': { width: 210, height: 297 },
        'A3': { width: 297, height: 420 },
        'A5': { width: 148, height: 210 },
        'Letter': { width: 215.9, height: 279.4 },
        'Legal': { width: 215.9, height: 355.6 },
        'Tabloid': { width: 279.4, height: 431.8 }
    };

    const dimsMM = formatDimensionsMM[format] || formatDimensionsMM['A4'];

    // Calculer les dimensions en pixels (1 inch = 25.4mm)
    const dpi = window.pdfBuilderCanvasSettings?.default_canvas_dpi || 96;
    const pixelsPerMM = dpi / 25.4;
    const dims = {
        width: Math.round(dimsMM.width * pixelsPerMM),
        height: Math.round(dimsMM.height * pixelsPerMM)
    };

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