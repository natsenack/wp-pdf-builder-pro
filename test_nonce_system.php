<?php
/**
 * Test script for advanced nonce system
 */

// Simulate WordPress environment
define('ABSPATH', __DIR__ . '/');
define('WPINC', 'wp-includes');

// Mock WordPress functions
function wp_create_nonce($action) {
    return 'test_nonce_' . $action . '_' . time();
}

function wp_verify_nonce($nonce, $action) {
    return strpos($nonce, 'test_nonce_' . $action) === 0;
}

function current_user_can($cap) {
    return true; // Assume user has permissions for testing
}

function wp_send_json_success($data) {
    echo "SUCCESS: " . json_encode($data) . "\n";
}

function wp_send_json_error($msg) {
    echo "ERROR: " . $msg . "\n";
}

function current_time($type) {
    return time();
}

// Include the test function
require_once 'plugin/pdf-builder-pro.php';

// Test the fresh nonce function
echo "Testing pdf_builder_get_fresh_nonce_ajax()...\n";

// Simulate POST request
$_POST = array();

// Call the function
pdf_builder_get_fresh_nonce_ajax();

echo "Test completed.\n";
?>