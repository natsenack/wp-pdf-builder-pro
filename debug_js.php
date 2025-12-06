<?php
// Définir les constantes nécessaires pour éviter les protections
define('ABSPATH', 'i:\\wp-pdf-builder-pro\\');
define('WPINC', 'wp-includes');

// Simuler les settings pour le test
$settings = [
    'pdf_builder_canvas_dpi' => '96',
    'pdf_builder_canvas_format' => 'A4',
    'pdf_builder_canvas_width' => '794',
    'pdf_builder_canvas_height' => '1123',
    'pdf_builder_canvas_bg_color' => '#ffffff',
    'pdf_builder_canvas_border_color' => '#cccccc',
    'pdf_builder_canvas_border_width' => '1',
    'pdf_builder_canvas_shadow_enabled' => '0',
    'pdf_builder_canvas_container_bg_color' => '#f5f5f5',
    'pdf_builder_canvas_guides_enabled' => '1',
    'pdf_builder_canvas_grid_enabled' => '1',
    'pdf_builder_canvas_grid_size' => '20',
    'pdf_builder_canvas_snap_to_grid' => '1',
    'pdf_builder_canvas_zoom_min' => '10',
    'pdf_builder_canvas_zoom_max' => '500',
    'pdf_builder_canvas_zoom_default' => '100',
    'pdf_builder_canvas_zoom_step' => '25',
    'pdf_builder_canvas_drag_enabled' => '1',
    'pdf_builder_canvas_resize_enabled' => '1',
    'pdf_builder_canvas_rotate_enabled' => '1',
    'pdf_builder_canvas_multi_select' => '1',
    'pdf_builder_canvas_selection_mode' => 'single',
    'pdf_builder_canvas_keyboard_shortcuts' => '1',
    'pdf_builder_canvas_export_format' => 'pdf',
    'pdf_builder_canvas_export_quality' => '90',
    'pdf_builder_canvas_export_transparent' => '0',
    'pdf_builder_canvas_fps_target' => '60',
    'pdf_builder_canvas_memory_limit_js' => '128',
    'pdf_builder_canvas_lazy_loading_editor' => '1',
    'pdf_builder_canvas_preload_critical' => '1',
    'pdf_builder_canvas_memory_limit_php' => '128',
    'pdf_builder_canvas_response_timeout' => '30',
    'pdf_builder_canvas_lazy_loading_plugin' => '1',
    'pdf_builder_canvas_debug_enabled' => '0',
    'pdf_builder_canvas_performance_monitoring' => '0',
    'pdf_builder_canvas_error_reporting' => '0'
];

ob_start();
include('plugin/resources/templates/admin/settings-parts/settings-contenu.php');
$content = ob_get_clean();

preg_match('/<script>(.*?)<\/script>/s', $content, $matches);
if (isset($matches[1])) {
    $js = $matches[1];
    file_put_contents('debug_js.js', $js);
    echo 'JavaScript extracted to debug_js.js (' . strlen($js) . ' chars)' . PHP_EOL;

    // Count lines
    $lines = explode("\n", $js);
    echo 'Lines in JS: ' . count($lines) . PHP_EOL;

    // Look for potential regex issues around line 3005
    if (count($lines) > 3000) {
        echo 'Line 3000-3010:' . PHP_EOL;
        for ($i = 2995; $i < min(3015, count($lines)); $i++) {
            echo ($i+1) . ': ' . $lines[$i] . PHP_EOL;
        }
    }
} else {
    echo 'No script tag found' . PHP_EOL;
}
?>