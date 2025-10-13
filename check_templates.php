<?php
require_once('wp-load.php');
global $wpdb;

$table = $wpdb->prefix . 'pdf_builder_templates';
$templates = $wpdb->get_results("SELECT id, name, is_default FROM $table", ARRAY_A);

echo "Templates trouvés:\n";
foreach($templates as $template) {
    echo "- ID: {$template['id']}, Name: {$template['name']}, Default: " . ($template['is_default'] ? 'YES' : 'NO') . "\n";
}
?>