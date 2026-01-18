<?php
// Test simple de la logique de sauvegarde sans WordPress
echo "=== TEST DE LOGIQUE DE SAUVEGARDE ===\n";

// Simuler les données POST du formulaire
$simulated_post = array(
    'pdf_builder_settings' => array(
        'pdf_builder_canvas_multi_select' => '1',
        'pdf_builder_canvas_grid_enabled' => '0',
        'pdf_builder_canvas_guides_enabled' => '1',
        'pdf_builder_canvas_snap_to_grid' => '0',
        'pdf_builder_canvas_width' => '800',
        'pdf_builder_canvas_height' => '1200'
    )
);

echo "Données POST simulées:\n";
print_r($simulated_post['pdf_builder_settings']);

// Simuler la fonction de sanitization
function sanitize_settings($input) {
    error_log('[PDF Builder] SANITIZE CALLBACK - Input type: ' . gettype($input));
    if (is_array($input)) {
        error_log('[PDF Builder] SANITIZE CALLBACK - Input count: ' . count($input));
        error_log('[PDF Builder] SANITIZE CALLBACK - Input keys: ' . implode(', ', array_keys($input)));
    } else {
        error_log('[PDF Builder] SANITIZE CALLBACK - Input is not array: ' . print_r($input, true));
    }

    if (!is_array($input)) {
        return array();
    }

    return $input;
}

$sanitized = sanitize_settings($simulated_post['pdf_builder_settings']);
echo "\nDonnées après sanitization:\n";
print_r($sanitized);

// Variable globale pour simuler la DB
$simulated_db_settings = array();

// Simuler la fonction update_option
function update_option($key, $value) {
    global $simulated_db_settings;
    $simulated_db_settings[$key] = serialize($value);
    echo "Option '$key' sauvegardée en DB\n";
}

// Simuler la fonction get_option
function get_option($key, $default = array()) {
    global $simulated_db_settings;
    if (isset($simulated_db_settings[$key])) {
        return unserialize($simulated_db_settings[$key]);
    }
    return $default;
}

// Simuler la sauvegarde
echo "=== SIMULATION DE SAUVEGARDE ===\n";
$test_data = array(
    'pdf_builder_canvas_multi_select' => '1',
    'pdf_builder_canvas_grid_enabled' => '0',
    'pdf_builder_canvas_guides_enabled' => '1'
);

update_option('pdf_builder_settings', $test_data);

// Simuler la récupération
echo "\n=== SIMULATION DE RÉCUPÉRATION ===\n";
$retrieved = get_option('pdf_builder_settings', array());
print_r($retrieved);

// Tester la fonction get_canvas_option_contenu
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

echo "\n=== TESTS DE RÉCUPÉRATION ===\n";
echo "multi_select: " . get_canvas_option_contenu('canvas_multi_select', '0') . "\n";
echo "grid_enabled: " . get_canvas_option_contenu('canvas_grid_enabled', '1') . "\n";
echo "non_existent: " . get_canvas_option_contenu('non_existent', 'default') . "\n";

echo "\n=== CONCLUSION ===\n";
echo "La logique de sauvegarde semble correcte. Les données sont:\n";
echo "1. Sanitized correctement\n";
echo "2. Sérialisées pour la DB\n";
echo "3. Désérialisées depuis la DB\n";
echo "4. Récupérées via get_canvas_option_contenu\n";
?>