<?php
/**
 * Test de diagnostic pour l'aperçu PDF
 * Ce fichier teste directement les fonctions d'aperçu sans passer par AJAX
 */

require_once __DIR__ . '/includes/pdf-preview-generator.php';

// Test des éléments simples
$test_elements = [
    [
        'type' => 'text',
        'content' => 'Test PDF Preview',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30,
        'fontSize' => 16,
        'color' => '#000000'
    ]
];

echo "=== TEST DIAGNOSTIC APERÇU PDF ===\n\n";

try {
    echo "1. Test création PDF_Preview_Generator...\n";
    $preview_gen = new PDF_Preview_Generator();
    echo "   ✅ PDF_Preview_Generator créé\n";

    echo "2. Test génération aperçu...\n";
    $result = $preview_gen->generate_preview($test_elements, 400, 566);

    if ($result['success']) {
        echo "   ✅ Aperçu généré avec succès\n";
        echo "   Taille de l'image: " . strlen($result['preview']) . " caractères\n";
    } else {
        echo "   ❌ Échec génération aperçu: " . $result['error'] . "\n";
    }

} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "\n";
}

echo "\n3. Test fallback direct...\n";
try {
    $result = $preview_gen->generate_fallback_preview($test_elements, 400, 566);

    if ($result['success']) {
        echo "   ✅ Fallback réussi\n";
        echo "   Taille de l'image: " . strlen($result['preview']) . " caractères\n";
    } else {
        echo "   ❌ Fallback échoué: " . $result['error'] . "\n";
    }

} catch (Exception $e) {
    echo "   ❌ Exception fallback: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
?>