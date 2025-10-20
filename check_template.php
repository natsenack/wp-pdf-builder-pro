<?php
require_once 'wp-load.php';

$template_id = 1;
$elements = get_post_meta($template_id, 'pdf_builder_elements', true);

echo 'Template ID 1 exists: ' . (get_post($template_id) ? 'YES' : 'NO') . PHP_EOL;
echo 'Elements type: ' . gettype($elements) . PHP_EOL;
echo 'Elements count: ' . (is_array($elements) ? count($elements) : 'N/A') . PHP_EOL;

if (is_array($elements) && count($elements) > 0) {
    echo 'First element: ' . json_encode($elements[0]) . PHP_EOL;
} else {
    echo 'No elements found' . PHP_EOL;
}
?>