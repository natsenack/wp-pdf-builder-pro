<?php
// Test de sauvegarde des paramètres canvas
require_once 'plugin/pdf-builder-pro.php';

// Simuler une sauvegarde
echo "Test de sauvegarde des paramètres canvas\n";
echo "=======================================\n\n";

// Valeurs de test
$test_values = [
    'pdf_builder_canvas_drag_enabled' => '0', // Désactiver
    'pdf_builder_canvas_shadow_enabled' => '0', // Désactiver
    'pdf_builder_canvas_autosave_enabled' => '0', // Désactiver
];

echo "Valeurs avant sauvegarde:\n";
foreach ($test_values as $option => $value) {
    $current = get_option($option, 'DEFAULT');
    echo "  $option: $current\n";
}

echo "\nSauvegarde des nouvelles valeurs...\n";
foreach ($test_values as $option => $value) {
    update_option($option, $value);
    echo "  Sauvegardé $option = $value\n";
}

echo "\nValeurs après sauvegarde:\n";
foreach ($test_values as $option => $value) {
    $current = get_option($option, 'DEFAULT');
    echo "  $option: $current\n";
    if ($current !== $value) {
        echo "  ❌ ERREUR: Valeur non sauvegardée correctement!\n";
    } else {
        echo "  ✅ OK\n";
    }
}

echo "\nTest terminé.\n";
?>