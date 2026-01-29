<?php
require_once('wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table LIMIT 5", ARRAY_A);
echo 'Templates in database:' . PHP_EOL;
foreach ($templates as $template) {
    echo 'ID: ' . $template['id'] . ', Name: ' . $template['name'] . PHP_EOL;
    $data = json_decode($template['template_data'], true);
    echo '  Template data keys: ' . implode(', ', array_keys($data)) . PHP_EOL;
    if (isset($data['name'])) echo '  Data name: ' . $data['name'] . PHP_EOL;
    if (isset($data['description'])) echo '  Data description: ' . $data['description'] . PHP_EOL;
    echo PHP_EOL;
}
?>