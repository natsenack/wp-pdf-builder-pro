<?php
// Test script pour vérifier la fonction get_canvas_option_contenu
require_once 'I:/wp-pdf-builder-pro-V2/plugin/bootstrap.php';

// Simuler des settings
update_option('pdf_builder_settings', array(
    'pdf_builder_canvas_multi_select' => '1',
    'pdf_builder_canvas_show_preview' => '0'
));

// Tester la fonction
function get_canvas_option_contenu($key, $default = '') {
    $option_key = 'pdf_builder_' . $key;
    // Lire depuis l'array de settings
    $settings = get_option('pdf_builder_settings', array());
    $value = isset($settings[$option_key]) ? $settings[$option_key] : null;

    if ($value === null) {
        $value = $default;
        error_log("[PDF Builder] PAGE_LOAD - {$key}: OPTION_NOT_FOUND - using default '{$default}' - KEY: {$option_key}");
    } else {
        error_log("[PDF Builder] PAGE_LOAD - {$key}: FOUND_DB_VALUE '{$value}' - KEY: {$option_key}");
    }

    return $value;
}

echo "Test canvas_multi_select: " . get_canvas_option_contenu('canvas_multi_select', '0') . "\n";
echo "Test canvas_show_preview: " . get_canvas_option_contenu('canvas_show_preview', '1') . "\n";
echo "Test non_existent: " . get_canvas_option_contenu('non_existent', 'default_value') . "\n";
?>