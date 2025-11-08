<?php
// Test direct de sauvegarde WordPress
echo "=== TEST SAUVEGARDE WORDPRESS ===\n\n";

define('ABSPATH', '/var/www/html/');
require_once ABSPATH . 'wp-load.php';

echo "WordPress chargé\n";

// Tester la sauvegarde directe
$test_data = [
    'default_canvas_width' => 1200,
    'default_canvas_height' => 1600,
    'show_grid' => true,
    'grid_size' => 25
];

echo "Données à sauvegarder :\n";
print_r($test_data);
echo "\n";

// Sauvegarder
$result = update_option('pdf_builder_canvas_settings', $test_data);
echo "update_option result: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";

// Récupérer
$saved_data = get_option('pdf_builder_canvas_settings', []);
echo "Données récupérées :\n";
print_r($saved_data);
echo "\n";

// Vérifier les valeurs spécifiques
echo "Vérifications :\n";
echo "- default_canvas_width: " . ($saved_data['default_canvas_width'] ?? 'NOT SET') . " (expected: 1200)\n";
echo "- show_grid: " . (($saved_data['show_grid'] ?? false) ? 'true' : 'false') . " (expected: true)\n";
echo "- grid_size: " . ($saved_data['grid_size'] ?? 'NOT SET') . " (expected: 25)\n";
?>