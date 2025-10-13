<?php
/**
 * Test script to debug customer_info not showing in metabox preview
 */

// Include WordPress
require_once '../../../../wp-load.php';

if (!class_exists('PDF_Builder_Admin')) {
    echo "PDF_Builder_Admin class not found\n";
    exit;
}

$admin = new PDF_Builder_Admin();

// Test loading template for a sample order
$order_id = 1; // Change this to a real order ID
$order = wc_get_order($order_id);

if (!$order) {
    echo "Order not found. Please set a valid order_id.\n";
    exit;
}

echo "Testing template loading for order ID: $order_id\n";
echo "Order status: " . $order->get_status() . "\n";

// Test detect_document_type
$document_type = $admin->detect_document_type($order->get_status());
echo "Detected document type: $document_type\n";

// Test template loading logic (simulate the logic from render_woocommerce_order_meta_box)
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Keywords for search
$keywords = [$document_type];
echo "Search keywords: " . implode(', ', $keywords) . "\n";

$default_template = null;

if (!empty($keywords)) {
    // Search for default template containing keywords
    $placeholders = str_repeat('%s,', count($keywords) - 1) . '%s';
    $sql = $wpdb->prepare(
        "SELECT id, name FROM $table_templates WHERE is_default = 1 AND (" .
        implode(' OR ', array_fill(0, count($keywords), 'LOWER(name) LIKE LOWER(%s)')) .
        ") LIMIT 1",
        array_map(function($keyword) { return '%' . $keyword . '%'; }, $keywords)
    );
    $default_template = $wpdb->get_row($sql, ARRAY_A);
    echo "Template found by keywords: " . ($default_template ? $default_template['name'] : 'NONE') . "\n";
}

// If no specific template found, get any default template
if (!$default_template) {
    $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
    echo "Fallback default template: " . ($default_template ? $default_template['name'] : 'NONE') . "\n";
}

// Load template data
if ($default_template) {
    $template_data = $admin->load_template_robust($default_template['id']);
    echo "Template data loaded successfully\n";

    // Check if customer_info element exists
    $has_customer_info = false;
    if (isset($template_data['pages']) && is_array($template_data['pages'])) {
        foreach ($template_data['pages'] as $page) {
            if (isset($page['elements']) && is_array($page['elements'])) {
                foreach ($page['elements'] as $element) {
                    if (isset($element['type']) && $element['type'] === 'customer_info') {
                        $has_customer_info = true;
                        echo "✓ customer_info element found in template\n";
                        break 2;
                    }
                }
            }
        }
    }

    if (!$has_customer_info) {
        echo "✗ customer_info element NOT found in template\n";
        echo "Available elements:\n";
        if (isset($template_data['pages']) && is_array($template_data['pages'])) {
            foreach ($template_data['pages'] as $page) {
                if (isset($page['elements']) && is_array($page['elements'])) {
                    foreach ($page['elements'] as $element) {
                        echo "  - " . ($element['type'] ?? 'unknown') . " (" . ($element['id'] ?? 'no-id') . ")\n";
                    }
                }
            }
        }
    }
} else {
    echo "No default template found in database\n";
    echo "Using hardcoded default template...\n";

    $template_data = $admin->get_default_invoice_template();
    $has_customer_info = false;
    if (isset($template_data['pages']) && is_array($template_data['pages'])) {
        foreach ($template_data['pages'] as $page) {
            if (isset($page['elements']) && is_array($page['elements'])) {
                foreach ($page['elements'] as $element) {
                    if (isset($element['type']) && $element['type'] === 'customer_info') {
                        $has_customer_info = true;
                        echo "✓ customer_info element found in hardcoded template\n";
                        break 2;
                    }
                }
            }
        }
    }

    if (!$has_customer_info) {
        echo "✗ customer_info element NOT found in hardcoded template\n";
    }
}

echo "\nTest completed.\n";