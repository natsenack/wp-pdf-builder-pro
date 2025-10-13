<?php
// Test AJAX Endpoints for PDF Builder Pro
echo "🧪 TESTING PDF BUILDER PRO AJAX ENDPOINTS\n";
echo "==========================================\n\n";

// Chemin vers WordPress
$wp_load_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php',
    'C:/xampp/htdocs/wordpress/wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        echo "✅ WordPress loaded from: $path\n";
        break;
    }
}

if (!$wp_loaded) {
    die("❌ Could not find wp-load.php\n");
}

// Test basic AJAX functionality
echo "\n🔍 Testing AJAX functionality...\n";

// Check if admin-ajax.php exists
$admin_ajax_path = ABSPATH . 'wp-admin/admin-ajax.php';
if (file_exists($admin_ajax_path)) {
    echo "✅ admin-ajax.php exists at: $admin_ajax_path\n";
} else {
    echo "❌ admin-ajax.php not found\n";
}

// Test nonce generation
$nonce = wp_create_nonce('pdf_builder_nonce');
echo "🔑 Generated nonce: " . substr($nonce, 0, 10) . "...\n";

// Test AJAX URL
$ajax_url = admin_url('admin-ajax.php');
echo "🔗 AJAX URL: $ajax_url\n";

// Check if PDF Builder plugin is active
if (is_plugin_active('pdf-builder-pro/pdf-builder-pro.php')) {
    echo "✅ PDF Builder Pro plugin is active\n";
} else {
    echo "❌ PDF Builder Pro plugin is NOT active\n";
}

// Test database connection
global $wpdb;
try {
    $test_query = $wpdb->get_var("SELECT 1");
    if ($test_query === "1") {
        echo "✅ Database connection OK\n";
    } else {
        echo "❌ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Check PDF Builder tables
$tables_to_check = [
    $wpdb->prefix . 'pdf_builder_templates',
    $wpdb->prefix . 'pdf_builder_elements',
    $wpdb->prefix . 'pdf_builder_settings'
];

echo "\n📊 Checking PDF Builder tables:\n";
foreach ($tables_to_check as $table) {
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
    if ($table_exists) {
        echo "✅ Table exists: $table\n";
        // Count records
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo "   📈 Records: $count\n";
    } else {
        echo "❌ Table missing: $table\n";
    }
}

echo "\n🏁 AJAX Endpoint test completed!\n";
?>