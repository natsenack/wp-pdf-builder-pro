<?php
/**
 * Test simplifié du système d'aperçu - sans dépendances WordPress
 */

echo "====== TEST APERÇU SIMPLIFIÉ ======\n\n";

// 1. Tester les données de template
$templateData = [
    'id' => 1,
    'name' => 'Template Test',
    'elements' => [
        [
            'id' => 'elem_1',
            'type' => 'text',
            'content' => 'Texte de test',
            'x' => 10,
            'y' => 10,
            'width' => 100,
            'height' => 20,
        ],
        [
            'id' => 'elem_2',
            'type' => 'rectangle',
            'x' => 10,
            'y' => 40,
            'width' => 100,
            'height' => 50,
            'color' => '#FF0000',
        ],
    ],
    'width' => 210,
    'height' => 297,
];

echo "1. TEMPLATE DATA:\n";
echo json_encode($templateData, JSON_PRETTY_PRINT) . "\n\n";

// 2. Tester validateTemplateData
require_once('plugin/src/utilities/TemplateValidator.php');
$validator = new \WP_PDF_Builder\Utilities\TemplateValidator();

echo "2. VALIDATION:\n";
try {
    $validated = $validator->validateTemplateData($templateData);
    echo "✅ Validation OK\n";
    echo "Resultat:\n";
    echo json_encode($validated, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "❌ Erreur validation: " . $e->getMessage() . "\n\n";
}

// 3. Vérifier la structure finale
echo "3. STRUCTURE FINALE:\n";
if (isset($validated['elements'])) {
    echo "✅ Elements trouvés: " . count($validated['elements']) . "\n";
    foreach ($validated['elements'] as $elem) {
        echo "  - {$elem['type']}: {$elem['id']}\n";
    }
} elseif (isset($validated['template']['elements'])) {
    echo "✅ Elements dans template: " . count($validated['template']['elements']) . "\n";
    foreach ($validated['template']['elements'] as $elem) {
        echo "  - {$elem['type']}: {$elem['id']}\n";
    }
} else {
    echo "❌ Aucun élément trouvé!\n";
}

echo "\n====== TEST TERMINÉ ======\n";
?>
