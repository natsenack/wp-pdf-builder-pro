<?php
/**
 * Debug script for canvas selection mode
 * Add this to your WordPress installation and access it via browser
 */

if (!defined('ABSPATH')) {
    die('Direct access forbidden');
}

echo '<h1>Canvas Selection Mode Debug</h1>';

// Check current option value
$current_mode = get_option('pdf_builder_canvas_selection_mode', 'click');
echo '<p>Current canvas_selection_mode option: <strong>' . esc_html($current_mode) . '</strong></p>';

// Check if window.pdfBuilderCanvasSettings is properly set
echo '<h2>Canvas Settings from settings-canvas-params.php</h2>';
$canvas_settings_js = [
    'canvas_width' => intval(get_option('pdf_builder_canvas_width', 794)),
    'canvas_height' => intval(get_option('pdf_builder_canvas_height', 1123)),
    'canvas_unit' => get_option('pdf_builder_canvas_unit', 'px'),
    'canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
    'canvas_background_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
    'canvas_show_transparency' => get_option('pdf_builder_canvas_show_transparency', false) == '1',
    'container_background_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
    'container_show_transparency' => get_option('pdf_builder_canvas_container_show_transparency', false) == '1',
    'border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
    'border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
    'shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0') == '1',
    'margin_top' => intval(get_option('pdf_builder_canvas_margin_top', 28)),
    'margin_right' => intval(get_option('pdf_builder_canvas_margin_right', 28)),
    'margin_bottom' => intval(get_option('pdf_builder_canvas_margin_bottom', 10)),
    'margin_left' => intval(get_option('pdf_builder_canvas_margin_left', 10)),
    'show_margins' => get_option('pdf_builder_canvas_show_margins', false) == '1',
    'show_grid' => get_option('pdf_builder_canvas_grid_enabled', '1') == '1',
    'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
    'grid_color' => get_option('pdf_builder_canvas_grid_color', '#e0e0e0'),
    'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') == '1',
    'snap_to_elements' => get_option('pdf_builder_canvas_snap_to_elements', false) == '1',
    'snap_tolerance' => intval(get_option('pdf_builder_canvas_snap_tolerance', 5)),
    'show_guides' => get_option('pdf_builder_canvas_guides_enabled', '1') == '1',
    'default_zoom' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
    'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
    'min_zoom' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
    'max_zoom' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
    'zoom_with_wheel' => get_option('pdf_builder_canvas_zoom_with_wheel', '1') == '1',
    'pan_with_mouse' => get_option('pdf_builder_canvas_pan_enabled', '1') == '1',
    'show_resize_handles' => get_option('pdf_builder_canvas_show_resize_handles', '1') == '1',
    'handle_size' => intval(get_option('pdf_builder_canvas_handle_size', 8)),
    'handle_color' => get_option('pdf_builder_canvas_handle_color', '#007cba'),
    'enable_rotation' => get_option('pdf_builder_canvas_rotate_enabled', '1') == '1',
    'rotation_step' => intval(get_option('pdf_builder_canvas_rotation_step', 15)),
    'multi_select' => get_option('pdf_builder_canvas_multi_select', '1') == '1',
    'copy_paste_enabled' => get_option('pdf_builder_canvas_copy_paste_enabled', '1') == '1',
    'export_quality' => intval(get_option('pdf_builder_canvas_export_quality', 90)),
    'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
    'compress_images' => get_option('pdf_builder_canvas_compress_images', '1') == '1',
    'image_quality' => intval(get_option('pdf_builder_canvas_image_quality', 85)),
    'max_image_size' => intval(get_option('pdf_builder_canvas_max_image_size', 2048)),
    'include_metadata' => get_option('pdf_builder_canvas_include_metadata', '1') == '1',
    'pdf_author' => get_option('pdf_builder_canvas_pdf_author', 'PDF Builder Pro'),
    'pdf_subject' => get_option('pdf_builder_canvas_pdf_subject', ''),
    'auto_crop' => get_option('pdf_builder_canvas_auto_crop', false) == '1',
    'embed_fonts' => get_option('pdf_builder_canvas_embed_fonts', '1') == '1',
    'optimize_for_web' => get_option('pdf_builder_canvas_optimize_for_web', '1') == '1',
    'enable_hardware_acceleration' => get_option('pdf_builder_canvas_enable_hardware_acceleration', '1') == '1',
    'limit_fps' => get_option('pdf_builder_canvas_limit_fps', '1') == '1',
    'max_fps' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
    'auto_save_enabled' => get_option('pdf_builder_canvas_auto_save', '1') == '1',
    'auto_save_interval' => intval(get_option('pdf_builder_canvas_auto_save_interval', 30)),
    'auto_save_versions' => intval(get_option('pdf_builder_canvas_auto_save_versions', 10)),
    'undo_levels' => intval(get_option('pdf_builder_canvas_undo_levels', 50)),
    'redo_levels' => intval(get_option('pdf_builder_canvas_redo_levels', 50)),
    'enable_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') == '1',
    'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
    'debug_mode' => get_option('pdf_builder_canvas_debug_mode', false) == '1',
    'show_fps' => get_option('pdf_builder_canvas_show_fps', false) == '1'
];

echo '<pre>';
echo 'canvas_selection_mode in settings: ' . (isset($canvas_settings_js['canvas_selection_mode']) ? $canvas_settings_js['canvas_selection_mode'] : 'NOT SET') . "\n";
echo 'Full canvas_settings_js array: ' . "\n";
print_r($canvas_settings_js);
echo '</pre>';

// JavaScript to check window.pdfBuilderCanvasSettings
echo '<h2>JavaScript Debug</h2>';
echo '<p>Open browser console and check:</p>';
echo '<pre>console.log(window.pdfBuilderCanvasSettings?.canvas_selection_mode);</pre>';

echo '<script>
console.log("window.pdfBuilderCanvasSettings:", window.pdfBuilderCanvasSettings);
console.log("canvas_selection_mode:", window.pdfBuilderCanvasSettings?.canvas_selection_mode);
</script>';
?>