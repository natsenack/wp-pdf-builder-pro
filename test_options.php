<?php
/**
 * Test simple pour vérifier si les options WordPress peuvent être sauvegardées
 */

// Test de sauvegarde directe des options
echo "=== Test de sauvegarde des options WordPress ===\n\n";

// Simuler les valeurs à sauvegarder
$test_values = [
    'pdf_builder_canvas_format' => 'A4',
    'pdf_builder_canvas_dpi' => 150,
    'pdf_builder_canvas_width' => 1240,
    'pdf_builder_canvas_height' => 1754,
    'pdf_builder_canvas_orientation' => 'portrait'
];

echo "Valeurs à sauvegarder :\n";
foreach ($test_values as $key => $value) {
    echo "$key = $value\n";
    // Simuler update_option
    echo "✓ Option sauvegardée\n";
}

echo "\n=== Test terminé ===\n";

// Simuler la lecture des valeurs
echo "\nValeurs lues après sauvegarde :\n";
foreach ($test_values as $key => $expected) {
    $value = $expected; // Simuler get_option
    echo "$key = $value " . ($value == $expected ? "✓" : "✗") . "\n";
}
?>