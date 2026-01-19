<?php
/**
 * Test script to check wp_pdf_builder_settings table
 */
require_once('plugin/bootstrap.php');

global $wpdb;
$table_name = $wpdb->prefix . 'pdf_builder_settings';

echo "Checking table: $table_name\n";

$exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
echo "Table $table_name " . ($exists ? 'EXISTS' : 'DOES NOT EXIST') . "\n";

if ($exists) {
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "Records in table: $count\n";

    $sample = $wpdb->get_results("SELECT option_name, LEFT(option_value, 50) as value_preview FROM $table_name LIMIT 5");
    foreach ($sample as $row) {
        echo "- {$row->option_name}: {$row->value_preview}...\n";
    }

    // Test specific canvas settings
    echo "\nTesting canvas settings:\n";
    $canvas_settings = [
        'pdf_builder_canvas_width',
        'pdf_builder_canvas_height',
        'pdf_builder_canvas_grid_enabled'
    ];

    foreach ($canvas_settings as $setting) {
        $value = pdf_builder_get_option($setting, 'NOT_SET');
        echo "- $setting: $value\n";
    }
} else {
    echo "Table does not exist, creating it...\n";
    \PDF_Builder\Database\Settings_Table_Manager::create_table();
    echo "Table created.\n";
}