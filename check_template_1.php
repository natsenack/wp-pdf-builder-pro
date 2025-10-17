<?php
include 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$template = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id = %d', 1));
if ($template) {
    echo 'Template: ' . $template->name . PHP_EOL;
    $elements = json_decode($template->elements, true);
    foreach ($elements as $el) {
        if ($el['type'] === 'product_table') {
            echo 'Table found - Style: ' . ($el['tableStyle'] ?? 'none') . PHP_EOL;
            echo 'Background: ' . ($el['backgroundColor'] ?? 'none') . PHP_EOL;
            break;
        }
    }
} else {
    echo 'Template not found';
}