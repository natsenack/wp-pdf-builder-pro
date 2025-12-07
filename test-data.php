<?php
// Bypass direct access protection for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/plugin/bootstrap.php';

echo "=== État actuel des données ===\n\n";

echo "1. Données dans pdf_builder_order_status_templates:\n";
$data = get_option('pdf_builder_order_status_templates', []);
var_dump($data);

echo "\n2. Données dans pdf_builder_settings (templates):\n";
$settings = get_option('pdf_builder_settings', []);
$templates_in_settings = $settings['pdf_builder_order_status_templates'] ?? null;
var_dump($templates_in_settings);

echo "\n3. Toutes les clés dans pdf_builder_settings:\n";
$keys = array_keys($settings);
sort($keys);
foreach ($keys as $key) {
    echo "- $key\n";
}
?>