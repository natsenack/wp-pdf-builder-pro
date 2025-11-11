<?php
// Script pour vérifier les données du template en base
require_once '../../../wp-load.php';

global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$result = $wpdb->get_row("SELECT * FROM $table WHERE id = 2", ARRAY_A);

if ($result) {
    echo 'Template found in custom table' . PHP_EOL;
    echo 'Template data length: ' . strlen($result['template_data']) . PHP_EOL;

    $data = json_decode($result['template_data'], true);
    if ($data) {
        echo 'JSON decode successful' . PHP_EOL;
        if (isset($data['elements'])) {
            echo 'Elements count: ' . count($data['elements']) . PHP_EOL;
            foreach ($data['elements'] as $i => $el) {
                if (isset($el['type']) && $el['type'] === 'order_number') {
                    echo "Order element $i: " . json_encode([
                        'id' => $el['id'] ?? 'missing',
                        'contentAlign' => $el['contentAlign'] ?? 'missing',
                        'labelPosition' => $el['labelPosition'] ?? 'missing',
                        'type' => $el['type'] ?? 'missing'
                    ]) . PHP_EOL;
                }
            }
        } else {
            echo 'No elements key in data' . PHP_EOL;
        }
    } else {
        echo 'JSON decode failed: ' . json_last_error_msg() . PHP_EOL;
        echo 'Raw data (first 500 chars): ' . substr($result['template_data'], 0, 500) . PHP_EOL;
    }
} else {
    echo 'Template not found in custom table' . PHP_EOL;

    // Check wp_posts
    $post = get_post(2);
    if ($post && $post->post_type === 'pdf_template') {
        echo 'Template found in wp_posts' . PHP_EOL;
        $meta = get_post_meta(2, '_pdf_template_data', true);
        if ($meta) {
            echo 'Meta data length: ' . strlen($meta) . PHP_EOL;
            $data = json_decode($meta, true);
            if ($data && isset($data['elements'])) {
                foreach ($data['elements'] as $i => $el) {
                    if (isset($el['type']) && $el['type'] === 'order_number') {
                        echo "Order element $i: " . json_encode([
                            'contentAlign' => $el['contentAlign'] ?? 'missing',
                            'labelPosition' => $el['labelPosition'] ?? 'missing'
                        ]) . PHP_EOL;
                    }
                }
            }
        }
    } else {
        echo 'Template not found in wp_posts either' . PHP_EOL;
    }
}
?>