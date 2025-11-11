<?php
require_once('wp-load.php');

global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$result = $wpdb->get_row("SELECT * FROM $table WHERE id = 2", ARRAY_A);

if ($result) {
    echo "Template found in DB:\n";
    echo "ID: " . $result['id'] . "\n";
    echo "Name: " . $result['name'] . "\n";
    echo "Updated: " . $result['updated_at'] . "\n";
    echo "Data length: " . strlen($result['template_data']) . "\n";

    $data = json_decode($result['template_data'], true);
    if ($data && isset($data['elements'])) {
        echo "Elements count: " . count($data['elements']) . "\n";
        foreach ($data['elements'] as $i => $element) {
            if (isset($element['type']) && $element['type'] === 'order_number') {
                echo "Order number element found at index $i:\n";
                echo "  contentAlign: " . ($element['contentAlign'] ?? 'NOT SET') . "\n";
                echo "  labelPosition: " . ($element['labelPosition'] ?? 'NOT SET') . "\n";
                echo "  All properties: " . json_encode($element) . "\n";
                break;
            }
        }
    } else {
        echo "No elements found in template data\n";
    }
} else {
    echo "Template not found in DB\n";
}
?>