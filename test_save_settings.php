<?php
// Test de sauvegarde et récupération des settings
require_once 'I:/wp-pdf-builder-pro-V2/plugin/bootstrap.php';

// Test 1: Sauvegarder des valeurs de test
echo "=== TEST DE SAUVEGARDE ===\n";
$test_settings = array(
    'pdf_builder_canvas_multi_select' => '1',
    'pdf_builder_canvas_grid_enabled' => '0',
    'pdf_builder_canvas_guides_enabled' => '1',
    'pdf_builder_canvas_snap_to_grid' => '0'
);

update_option('pdf_builder_settings', $test_settings);
echo "Settings sauvegardés:\n";
print_r($test_settings);

// Test 2: Récupérer les valeurs sauvegardées
echo "\n=== TEST DE RÉCUPÉRATION ===\n";
$retrieved_settings = get_option('pdf_builder_settings', array());
echo "Settings récupérés:\n";
print_r($retrieved_settings);

// Test 3: Tester la fonction get_canvas_option_contenu
echo "\n=== TEST DE LA FONCTION get_canvas_option_contenu ===\n";
function get_canvas_option_contenu($key, $default = '') {
    $option_key = 'pdf_builder_' . $key;
    $settings = get_option('pdf_builder_settings', array());
    $value = isset($settings[$option_key]) ? $settings[$option_key] : null;

    if ($value === null) {
        $value = $default;
        echo "[$key] NOT_FOUND - using default '$default'\n";
    } else {
        echo "[$key] FOUND '$value'\n";
    }

    return $value;
}

echo "Test multi_select: " . get_canvas_option_contenu('canvas_multi_select', '0') . "\n";
echo "Test grid_enabled: " . get_canvas_option_contenu('canvas_grid_enabled', '1') . "\n";
echo "Test guides_enabled: " . get_canvas_option_contenu('canvas_guides_enabled', '0') . "\n";
echo "Test snap_to_grid: " . get_canvas_option_contenu('canvas_snap_to_grid', '1') . "\n";
echo "Test non_existent: " . get_canvas_option_contenu('non_existent', 'default') . "\n";

// Test 4: Vérifier directement dans la DB
echo "\n=== VÉRIFICATION DIRECTE EN DB ===\n";
global $wpdb;
$db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_settings'));
if ($db_value) {
    $unserialized = unserialize($db_value);
    echo "Valeur DB (unserialized):\n";
    print_r($unserialized);
} else {
    echo "Aucune valeur trouvée en DB pour 'pdf_builder_settings'\n";
}
?>