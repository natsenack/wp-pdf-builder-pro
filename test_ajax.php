<?php
// Test AJAX call simulation
require_once 'plugin/pdf-builder-pro.php';

// Simulate POST data
$_POST = [
    'action' => 'wp_pdf_preview_image',
    'nonce' => wp_create_nonce('pdf_builder_order_actions'),
    'context' => 'editor',
    'template_data' => json_encode(['elements' => []]),
    'quality' => 150,
    'format' => 'png'
];

// Simulate logged in user
wp_set_current_user(1); // Admin user

// Call the AJAX handler
try {
    pdf_builder_handle_preview_ajax();
    echo "\n✅ AJAX call completed successfully\n";
} catch (Exception $e) {
    echo "\n❌ AJAX call failed: " . $e->getMessage() . "\n";
}
?>