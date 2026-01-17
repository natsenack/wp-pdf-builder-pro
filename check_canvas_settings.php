<?php
// Script temporaire pour vérifier les paramètres canvas dans la base de données
define('WP_USE_THEMES', false);
require_once('plugin/bootstrap.php');

global $wpdb;
$results = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'pdf_builder_canvas_%' ORDER BY option_name");

echo "Paramètres canvas dans la base de données:\n";
echo "========================================\n";

foreach ($results as $row) {
    $value = $row->option_value;
    if (strlen($value) > 50) {
        $value = substr($value, 0, 50) . '...';
    }
    echo $row->option_name . ': ' . $value . "\n";
}

echo "\nParamètres de navigation spécifiques:\n";
echo "===================================\n";

$nav_params = ['pdf_builder_canvas_grid_enabled', 'pdf_builder_canvas_guides_enabled', 'pdf_builder_canvas_snap_to_grid'];
foreach ($nav_params as $param) {
    $value = get_option($param, 'NOT_SET');
    echo $param . ': ' . $value . "\n";
}