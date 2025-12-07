<?php
// Test script to check shadow option value
require_once('../../../wp-load.php');

$shadow_enabled = get_option('pdf_builder_canvas_shadow_enabled', '0');
echo "Current shadow_enabled value: " . $shadow_enabled . "\n";

$all_options = wp_load_alloptions();
$matching_options = array_filter($all_options, function($key) {
    return strpos($key, 'pdf_builder_canvas') !== false;
}, ARRAY_FILTER_USE_KEY);

echo "All canvas options:\n";
foreach ($matching_options as $key => $value) {
    echo "$key: $value\n";
}
?>