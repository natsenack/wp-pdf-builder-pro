<?php
/**
 * Test du fallback uniquement
 */

require_once __DIR__ . '/includes/pdf-preview-generator.php';

$test_elements = [
    [
        'type' => 'text',
        'content' => 'Test Fallback',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30
    ]
];

echo "=== TEST FALLBACK DIRECT ===\n\n";

try {
    $preview_gen = new PDF_Preview_Generator();

    // Forcer le générateur à null pour tester le fallback
    $preview_gen->generator = null;

    $result = $preview_gen->generate_preview($test_elements, 400, 566);

    if ($result['success']) {
        echo "✅ Fallback réussi\n";
        echo "Taille image: " . strlen($result['preview']) . " caractères\n";
        echo "Fallback utilisé: " . ($result['fallback'] ? 'oui' : 'non') . "\n";
    } else {
        echo "❌ Fallback échoué: " . $result['error'] . "\n";
    }

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
?>