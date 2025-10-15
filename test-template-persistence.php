<?php
// Test script to verify template saving and loading
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PDF Builder Pro - Test de Sauvegarde/Chargement ===\n\n";

// Include WordPress - try different paths
$paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];

$loaded = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    echo "❌ Impossible de trouver wp-load.php. Chemins testés:\n";
    foreach ($paths as $path) {
        echo "  - $path: " . (file_exists($path) ? "existe" : "n'existe pas") . "\n";
    }
    exit;
}

if (!is_user_logged_in()) {
    wp_die('Vous devez être connecté pour accéder à ce test.');
}

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Get first template for testing
$template = $wpdb->get_row("SELECT id, name, template_data FROM $table_templates LIMIT 1", ARRAY_A);

if (!$template) {
    echo "❌ Aucun template trouvé dans la base de données.\n";
    exit;
}

echo "✅ Template trouvé: {$template['name']} (ID: {$template['id']})\n";

// Test JSON decoding
$template_data = json_decode($template['template_data'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur de décodage JSON: " . json_last_error_msg() . "\n";
    echo "Données brutes: " . substr($template['template_data'], 0, 200) . "...\n";
    exit;
}

echo "✅ JSON décodé avec succès\n";

// Check for elements
if (isset($template_data['elements']) && is_array($template_data['elements'])) {
    $element_count = count($template_data['elements']);
    echo "✅ {$element_count} éléments trouvés dans template_data.elements\n";

    // Show first element properties
    if ($element_count > 0) {
        $first_element = $template_data['elements'][0];
        echo "Premier élément - Type: " . ($first_element['type'] ?? 'N/A') . "\n";
        echo "Premier élément - ID: " . ($first_element['id'] ?? 'N/A') . "\n";
        echo "Premier élément - Couleur de fond: " . ($first_element['backgroundColor'] ?? 'N/A') . "\n";
    }
} elseif (isset($template_data['pages']) && is_array($template_data['pages'])) {
    echo "✅ Structure avec pages détectée\n";
    if (!empty($template_data['pages'])) {
        $first_page = $template_data['pages'][0];
        if (isset($first_page['elements']) && is_array($first_page['elements'])) {
            $element_count = count($first_page['elements']);
            echo "✅ {$element_count} éléments trouvés dans pages[0].elements\n";

            if ($element_count > 0) {
                $first_element = $first_page['elements'][0];
                echo "Premier élément - Type: " . ($first_element['type'] ?? 'N/A') . "\n";
                echo "Premier élément - ID: " . ($first_element['id'] ?? 'N/A') . "\n";
                echo "Premier élément - Couleur de fond: " . ($first_element['backgroundColor'] ?? 'N/A') . "\n";
            }
        }
    }
} else {
    echo "❌ Aucune structure d'éléments trouvée\n";
    echo "Clés disponibles: " . implode(', ', array_keys($template_data)) . "\n";
}

echo "\n=== Test terminé ===\n";
?>