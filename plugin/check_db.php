<?php
require_once('../../../wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$result = $wpdb->get_row('SELECT * FROM ' . $table . ' WHERE id = 2', ARRAY_A);
if ($result) {
    echo 'Template found in DB:' . PHP_EOL;
    echo 'ID: ' . $result['id'] . PHP_EOL;
    echo 'Name: ' . $result['name'] . PHP_EOL;
    echo 'Updated: ' . $result['updated_at'] . PHP_EOL;
    echo 'Data length: ' . strlen($result['template_data']) . PHP_EOL;
    $data = json_decode($result['template_data'], true);
    if ($data && isset($data['elements'])) {
        echo 'Elements count: ' . count($data['elements']) . PHP_EOL;
        foreach ($data['elements'] as $i => $element) {
            if (isset($element['type']) && $element['type'] === 'order_number') {
                echo 'Order number element found at index ' . $i . ':' . PHP_EOL;
                echo '  contentAlign: ' . ($element['contentAlign'] ?? 'NOT SET') . PHP_EOL;
                echo '  labelPosition: ' . ($element['labelPosition'] ?? 'NOT SET') . PHP_EOL;
                break;
            }
        }
    }
} else {
    echo 'Template not found in DB' . PHP_EOL;
}
?>