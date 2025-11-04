<?php
/**
 * Test script for PDF Builder Pro template save functionality
 * This script simulates the AJAX save operation to debug server errors
 */

// Simulate WordPress environment
define('ABSPATH', __DIR__ . '/../../../');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Include WordPress
require_once ABSPATH . 'wp-load.php';

// Check if user is logged in
if (!is_user_logged_in()) {
    die('User not logged in');
}

// Simulate POST data
$_POST = [
    'action' => 'pdf_builder_save_template',
    'nonce' => wp_create_nonce('pdf_builder_nonce'),
    'template_name' => 'Test Template',
    'template_data' => json_encode([
        'elements' => [
            [
                'id' => 'test-element',
                'type' => 'text',
                'x' => 10,
                'y' => 10,
                'width' => 100,
                'height' => 50,
                'properties' => [
                    'text' => 'Test Element',
                    'fontSize' => 12
                ]
            ]
        ],
        'canvas' => [
            'width' => 794,
            'height' => 1123
        ]
    ])
];

// Include the plugin
require_once __DIR__ . '/bootstrap.php';

// Try to call the save function
try {
    pdf_builder_ajax_save_template();
    echo "Save operation completed successfully\n";
} catch (Exception $e) {
    echo "Error during save: " . $e->getMessage() . "\n";
    error_log("Test save error: " . $e->getMessage());
}
?>