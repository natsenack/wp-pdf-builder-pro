<?php
// Test script to verify template save functionality
echo "=== Test de validation des données de template ===\n";

// Test data similar to what frontend sends
$test_data = [
    'elements' => [
        ['id' => 'test1', 'type' => 'text', 'content' => 'Test', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 50]
    ],
    'canvasWidth' => 210,  // A4 width in mm equivalent
    'canvasHeight' => 297, // A4 height in mm equivalent
    'version' => '1.0'
];

echo "Données de test :\n";
echo "- canvasWidth: {$test_data['canvasWidth']}\n";
echo "- canvasHeight: {$test_data['canvasHeight']}\n";
echo "- Elements: " . count($test_data['elements']) . "\n";

// Test JSON encoding
$json = json_encode($test_data);
echo "\nEncodage JSON: " . (json_last_error() === JSON_ERROR_NONE ? 'OK' : 'ERROR') . "\n";

// Test JSON decoding
$decoded = json_decode($json, true);
echo "Décodage JSON: " . (json_last_error() === JSON_ERROR_NONE ? 'OK' : 'ERROR') . "\n";

if ($decoded) {
    echo "Données décodées:\n";
    echo "- canvasWidth: {$decoded['canvasWidth']}\n";
    echo "- canvasHeight: {$decoded['canvasHeight']}\n";
}

// Test validation ranges
$width = (float) $test_data['canvasWidth'];
$height = (float) $test_data['canvasHeight'];

$width_valid = $width >= 50 && $width <= 2000;
$height_valid = $height >= 50 && $height <= 2000;

echo "\nValidation des dimensions:\n";
echo "- Width valid: " . ($width_valid ? 'YES' : 'NO') . " ($width)\n";
echo "- Height valid: " . ($height_valid ? 'YES' : 'NO') . " ($height)\n";

echo "\n=== Test terminé ===\n";
?>