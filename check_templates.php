<?php
require_once('wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table LIMIT 5");

echo 'Templates found: ' . count($templates) . PHP_EOL;
foreach($templates as $t) {
    echo 'ID: ' . $t->id . ' - Name: ' . $t->name . PHP_EOL;
    $data = json_decode($t->template_data, true);
    if(isset($data['elements'])) {
        echo '  Elements: ' . count($data['elements']) . PHP_EOL;
    } else {
        echo '  No elements' . PHP_EOL;
    }
}
?>