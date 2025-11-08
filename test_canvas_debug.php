<?php
// Test rapide pour vérifier les paramètres canvas
echo "=== TEST PARAMÈTRES CANVAS ===\n\n";

// Simuler une requête POST comme le ferait le JavaScript
$_POST = [
    'action' => 'pdf_builder_save_settings',
    'current_tab' => 'canvas',
    'pdf_builder_settings_nonce' => 'test_nonce',
    'default_canvas_width' => '1000',
    'default_canvas_height' => '1500',
    'show_grid' => '1',
    'snap_to_grid' => '1',
    'grid_size' => '20'
];

echo "Données POST simulées :\n";
print_r($_POST);
echo "\n";

// Charger WordPress et le plugin
define('ABSPATH', '/var/www/html/');
require_once ABSPATH . 'wp-load.php';
require_once 'wp-content/plugins/wp-pdf-builder-pro/plugin/bootstrap.php';

echo "WordPress chargé\n";

// Tester le Canvas Manager
if (class_exists('PDF_Builder_Canvas_Manager')) {
    echo "Canvas Manager trouvé\n";

    $manager = PDF_Builder_Canvas_Manager::get_instance();

    // Tester la récupération actuelle
    $current = $manager->get_canvas_settings();
    echo "Paramètres actuels :\n";
    echo "- default_canvas_width: " . ($current['default_canvas_width'] ?? 'NON DÉFINI') . "\n";
    echo "- show_grid: " . (($current['show_grid'] ?? false) ? 'true' : 'false') . "\n";
    echo "- grid_size: " . ($current['grid_size'] ?? 'NON DÉFINI') . "\n\n";

    // Tester la sauvegarde
    echo "Tentative de sauvegarde...\n";
    $test_data = [
        'default_canvas_width' => 1000,
        'show_grid' => true,
        'grid_size' => 20
    ];

    $result = $manager->save_canvas_settings($test_data);
    echo "Sauvegarde " . ($result ? "RÉUSSIE" : "ÉCHOUÉE") . "\n\n";

    // Vérifier après sauvegarde
    $after = $manager->get_canvas_settings();
    echo "Paramètres après sauvegarde :\n";
    echo "- default_canvas_width: " . ($after['default_canvas_width'] ?? 'NON DÉFINI') . "\n";
    echo "- show_grid: " . (($after['show_grid'] ?? false) ? 'true' : 'false') . "\n";
    echo "- grid_size: " . ($after['grid_size'] ?? 'NON DÉFINI') . "\n";

} else {
    echo "ERREUR: Canvas Manager non trouvé\n";
}
?>