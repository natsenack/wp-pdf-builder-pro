<?php
/**
 * Test script to check WordPress options values on server
 */

// Simulate WordPress environment
if (!function_exists('get_option')) {
    function get_option($key, $default = '') {
        // Simulate database values based on what we expect
        $simulated_values = [
            'pdf_builder_canvas_format' => 'A4',
            'pdf_builder_canvas_dpi' => '150',
            'pdf_builder_canvas_width' => '1240',
            'pdf_builder_canvas_height' => '1754',
            'pdf_builder_canvas_orientation' => 'portrait',
            // Add other options with defaults
            'pdf_builder_canvas_bg_color' => '#ffffff',
            'pdf_builder_canvas_border_color' => '#cccccc',
            'pdf_builder_canvas_border_width' => '1',
        ];

        return isset($simulated_values[$key]) ? $simulated_values[$key] : $default;
    }
}

echo "=== Vérification des options WordPress ===\n\n";

// Test the same options loaded in settings-canvas-params.php
$options_to_check = [
    'pdf_builder_canvas_format' => 'A4',
    'pdf_builder_canvas_dpi' => 96,
    'pdf_builder_canvas_width' => 794,
    'pdf_builder_canvas_height' => 1123,
    'pdf_builder_canvas_bg_color' => '#ffffff',
    'pdf_builder_canvas_border_color' => '#cccccc',
    'pdf_builder_canvas_border_width' => 1,
];

foreach ($options_to_check as $option => $default) {
    $value = get_option($option, $default);
    echo "$option = '$value'\n";
}

echo "\n=== Test des paramètres JavaScript ===\n\n";

// Simulate what settings-canvas-params.php generates
$canvas_settings_js = [
    'default_canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
    'default_canvas_dpi' => intval(get_option('pdf_builder_canvas_dpi', 96)),
    'canvas_bg_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
    'canvas_border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
    'canvas_border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
];

echo "Paramètres JavaScript générés :\n";
foreach ($canvas_settings_js as $key => $value) {
    echo "$key: '$value'\n";
}

echo "\n=== Mapping JavaScript ===\n\n";

// Simulate the elementMappings from syncFormElementsWithLoadedSettings
$elementMappings = [
    'canvas_format' => 'default_canvas_format',
    'canvas_dpi' => 'default_canvas_dpi',
    'canvas_bg_color' => 'canvas_bg_color',
    'canvas_border_color' => 'canvas_border_color',
    'canvas_border_width' => 'canvas_border_width',
];

echo "Mappings élément -> paramètre :\n";
foreach ($elementMappings as $element => $setting_key) {
    $value = isset($canvas_settings_js[$setting_key]) ? $canvas_settings_js[$setting_key] : 'NOT FOUND';
    echo "$element -> $setting_key = '$value'\n";
}
?>