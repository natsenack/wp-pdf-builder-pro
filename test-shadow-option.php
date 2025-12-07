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

// Test de sauvegarde temporaire
echo "\nTesting save operation...\n";
$test_value = '1';
update_option('pdf_builder_canvas_shadow_enabled', $test_value);
$after_save = get_option('pdf_builder_canvas_shadow_enabled', '0');
echo "After saving '$test_value': " . $after_save . "\n";

// Test avec une autre valeur
$test_value2 = '0';
update_option('pdf_builder_canvas_shadow_enabled', $test_value2);
$after_save2 = get_option('pdf_builder_canvas_shadow_enabled', '0');
echo "After saving '$test_value2': " . $after_save2 . "\n";
?>