<?php
/**
 * Test script for PDF Builder Pro settings
 * Tests basic functionality without full WordPress environment
 */

// Define minimal WordPress constants
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Mock WordPress functions
function get_option($key, $default = '') {
    // Mock some options for testing
    $mock_options = [
        'pdf_builder_canvas_format' => 'A4',
        'pdf_builder_canvas_dpi' => 96,
        'pdf_builder_canvas_width' => 794,
        'pdf_builder_canvas_height' => 1123,
        'pdf_builder_cache_enabled' => false,
        'pdf_builder_cache_ttl' => 3600,
    ];
    return $mock_options[$key] ?? $default;
}

function wp_verify_nonce($nonce, $action) {
    return true; // Mock successful nonce verification
}

function wp_send_json_success($data) {
    echo "SUCCESS: " . json_encode($data) . "\n";
}

function wp_send_json_error($data) {
    echo "ERROR: " . json_encode($data) . "\n";
}

function sanitize_text_field($text) {
    return trim($text);
}

// Load required files
echo "Loading Conventions.php...\n";
require_once __DIR__ . '/../../core/Conventions.php';

echo "Loading settings-ajax.php...\n";
require_once __DIR__ . '/settings-ajax.php';

echo "Testing PAPER_FORMATS constant...\n";
if (defined('\PDF_Builder\PAPER_FORMATS')) {
    echo "✅ PAPER_FORMATS constant is defined\n";
    print_r(\PDF_Builder\PAPER_FORMATS);
} else {
    echo "❌ PAPER_FORMATS constant is not defined\n";
}

echo "\nTesting AJAX functions...\n";

// Test cache clear function
echo "Testing pdf_builder_clear_cache_handler...\n";
$_POST = [
    'security' => 'test_nonce',
    'action' => 'pdf_builder_clear_cache'
];

try {
    pdf_builder_clear_cache_handler();
    echo "✅ pdf_builder_clear_cache_handler executed successfully\n";
} catch (Exception $e) {
    echo "❌ Error in pdf_builder_clear_cache_handler: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>