<?php
global $wpdb;
require_once('plugin/pdf-builder-pro.php');

$table = $wpdb->prefix . 'pdf_builder_templates';
$user_id = 1; // Admin user typically

$results = $wpdb->get_results($wpdb->prepare('SELECT id, name, is_default, user_id FROM ' . $table . ' WHERE user_id = %d', $user_id));

echo 'Templates pour user_id ' . $user_id . ':' . PHP_EOL;
foreach ($results as $row) {
    echo 'ID: ' . $row->id . ', Name: ' . $row->name . ', is_default: ' . $row->is_default . ', user_id: ' . $row->user_id . PHP_EOL;
}
echo 'Total: ' . count($results) . PHP_EOL;

// Compter seulement ceux avec is_default = 0
$count_custom = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE user_id = %d AND is_default = 0', $user_id));
echo 'Templates personnalisés (is_default=0): ' . $count_custom . PHP_EOL;

// Compter tous les templates
$count_all = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE user_id = %d', $user_id));
echo 'Tous les templates: ' . $count_all . PHP_EOL;
?>