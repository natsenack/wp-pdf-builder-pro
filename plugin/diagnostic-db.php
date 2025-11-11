<?php
/**
 * PDF Builder - Database Diagnostic Script
 * Check what's actually stored in the database for template ID 2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

// Only run in admin or for logged-in users
if (!is_user_logged_in()) {
    wp_die('You must be logged in to access this diagnostic');
}

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Get template with ID 2
$template = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", 2),
    ARRAY_A
);

echo "<h1>PDF Builder Database Diagnostic</h1>";
echo "<h2>Template ID 2</h2>";

if (!$template) {
    echo "<p style='color: red;'>Template ID 2 not found in database!</p>";
    return;
}

echo "<p><strong>Template Name:</strong> " . esc_html($template['name']) . "</p>";
echo "<p><strong>Created:</strong> " . esc_html($template['created_at']) . "</p>";
echo "<p><strong>Updated:</strong> " . esc_html($template['updated_at']) . "</p>";
echo "<p><strong>Data Length:</strong> " . strlen($template['template_data']) . " characters</p>";

// Check for specific properties
$raw_data = $template['template_data'];
$has_contentAlign = strpos($raw_data, 'contentAlign') !== false;
$has_labelPosition = strpos($raw_data, 'labelPosition') !== false;

echo "<h3>Property Check</h3>";
echo "<p><strong>Contains 'contentAlign':</strong> <span style='color: " . ($has_contentAlign ? 'green' : 'red') . ";'>" . ($has_contentAlign ? 'YES' : 'NO') . "</span></p>";
echo "<p><strong>Contains 'labelPosition':</strong> <span style='color: " . ($has_labelPosition ? 'green' : 'red') . ";'>" . ($has_labelPosition ? 'YES' : 'NO') . "</span></p>";

// Decode and analyze
$data = json_decode($raw_data, true);
if ($data === null) {
    echo "<p style='color: red;'>JSON decode failed: " . json_last_error_msg() . "</p>";
    return;
}

echo "<h3>Decoded Data Structure</h3>";
echo "<p><strong>Has 'elements' key:</strong> " . (isset($data['elements']) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Elements count:</strong> " . (isset($data['elements']) ? count($data['elements']) : 'N/A') . "</p>";

if (isset($data['elements'])) {
    echo "<h3>Order Number Elements</h3>";
    $order_elements = array_filter($data['elements'], function($el) {
        return isset($el['type']) && $el['type'] === 'order_number';
    });

    echo "<p><strong>Order number elements found:</strong> " . count($order_elements) . "</p>";

    foreach ($order_elements as $index => $element) {
        echo "<h4>Order Element " . ($index + 1) . "</h4>";
        echo "<p><strong>ID:</strong> " . (isset($element['id']) ? $element['id'] : 'N/A') . "</p>";
        echo "<p><strong>contentAlign:</strong> <span style='color: " . (isset($element['contentAlign']) ? 'green' : 'red') . ";'>" . (isset($element['contentAlign']) ? $element['contentAlign'] : 'MISSING') . "</span></p>";
        echo "<p><strong>labelPosition:</strong> <span style='color: " . (isset($element['labelPosition']) ? 'green' : 'red') . ";'>" . (isset($element['labelPosition']) ? $element['labelPosition'] : 'MISSING') . "</span></p>";

        echo "<h5>All Properties:</h5>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 200px;'>";
        foreach ($element as $key => $value) {
            echo htmlspecialchars($key) . ": " . htmlspecialchars(json_encode($value)) . "\n";
        }
        echo "</pre>";
    }
}

echo "<h3>Raw JSON Data</h3>";
echo "<details>";
echo "<summary>Click to expand raw JSON (first 1000 characters)</summary>";
echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px;'>";
echo htmlspecialchars(substr($raw_data, 0, 1000));
if (strlen($raw_data) > 1000) {
    echo "\n\n[... " . (strlen($raw_data) - 1000) . " more characters ...]";
}
echo "</pre>";
echo "</details>";
?>