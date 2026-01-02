<?php
// Try different paths for wp-load.php
$possible_paths = [
    __DIR__ . '/../wp-load.php',           // Parent directory
    __DIR__ . '/../../wp-load.php',        // Two levels up
    __DIR__ . '/../../../wp-load.php',     // Three levels up
    '/wp-load.php',                       // Root
];

$wp_load_found = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_load_found = true;
        echo "✅ Found wp-load.php at: $path\n";
        break;
    }
}

if (!$wp_load_found) {
    echo "❌ Could not find wp-load.php in any of these locations:\n";
    foreach ($possible_paths as $path) {
        echo "  - $path\n";
    }
    exit(1);
}

global $wpdb;
$table_name = $wpdb->prefix . 'pdf_builder_templates';

echo "Checking table: $table_name\n";

try {
    $result = $wpdb->get_results($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

    if (count($result) > 0) {
        echo "✅ Table exists: $table_name\n";

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "📊 Records count: $count\n";

        if ($count > 0) {
            $templates = $wpdb->get_results("SELECT id, name, LENGTH(template_data) as data_size FROM $table_name LIMIT 5");
            echo "📋 Sample templates:\n";
            foreach ($templates as $template) {
                echo "  ID: {$template->id}, Name: {$template->name}, Data size: {$template->data_size} chars\n";
            }
        } else {
            echo "⚠️  Table exists but is empty\n";
        }
    } else {
        echo "❌ Table does not exist: $table_name\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>