<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

require_once 'bootstrap.php';

$template_id = 1;
$template = get_post($template_id);

if ($template) {
    echo "Template exists: " . $template->post_title . "\n";
    $elements = get_post_meta($template_id, 'pdf_builder_elements', true);
    echo "Elements type: " . gettype($elements) . "\n";

    if (is_array($elements)) {
        echo "Elements count: " . count($elements) . "\n";
        if (count($elements) > 0) {
            echo "First element: " . json_encode($elements[0]) . "\n";
        }
    } else {
        echo "Elements value: " . var_export($elements, true) . "\n";
    }
} else {
    echo "Template not found\n";
}