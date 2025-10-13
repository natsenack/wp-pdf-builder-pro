<?php
/**
 * Test de simulation AJAX pour analyser les éléments React
 */

define('PDF_GENERATOR_TEST_MODE', true);

// Simuler des éléments EXACTEMENT comme ils seraient créés dans React
$test_elements = [
    [
        'id' => 'text-element-1',
        'type' => 'text',
        'text' => 'Hello World', // Certains éléments peuvent avoir 'text' au lieu de 'content'
        'x' => 50,
        'y' => 50,
        'width' => 150,
        'height' => 40,
        'fontSize' => 16,
        'fontFamily' => 'Arial, sans-serif',
        'fontWeight' => 'normal',
        'color' => '#000000',
        'backgroundColor' => '#d1d5db', // Couleur par défaut React - devrait être ignorée
        'borderWidth' => 1,
        'borderColor' => '#e5e7eb',
        'borderStyle' => 'solid',
        'padding' => 8,
        'textAlign' => 'left',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 1
    ],
    [
        'id' => 'text-element-2',
        'type' => 'text',
        'content' => 'Texte avec fond personnalisé', // D'autres peuvent avoir 'content'
        'x' => 50,
        'y' => 110,
        'width' => 200,
        'height' => 50,
        'fontSize' => 18,
        'fontFamily' => 'Helvetica, sans-serif',
        'fontWeight' => 'bold',
        'color' => '#ffffff',
        'backgroundColor' => '#dc2626', // Rouge personnalisé
        'borderWidth' => 3,
        'borderColor' => '#b91c1c',
        'borderStyle' => 'solid',
        'padding' => 12,
        'textAlign' => 'center',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 2
    ],
    [
        'id' => 'text-element-3',
        'type' => 'text',
        'content' => 'Texte transparent',
        'x' => 50,
        'y' => 180,
        'width' => 180,
        'height' => 35,
        'fontSize' => 14,
        'fontFamily' => 'Times New Roman, serif',
        'fontWeight' => 'normal',
        'color' => '#1f2937',
        'backgroundColor' => 'transparent', // Transparent - devrait être ignoré
        'borderWidth' => 0, // Pas de bordure
        'borderColor' => '#cccccc',
        'borderStyle' => 'solid',
        'padding' => 4,
        'textAlign' => 'left',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 3
    ]
];

// Inclure le générateur PDF
require_once __DIR__ . '/includes/pdf-generator.php';

// Créer le générateur et analyser les éléments
$generator = new PDF_Generator();

// Analyser les éléments comme dans generate_from_elements
echo "=== ANALYSE DES ÉLÉMENTS REACT ===\n";
echo "Nombre d'éléments: " . count($test_elements) . "\n\n";

foreach ($test_elements as $index => $element) {
    echo "Élément $index:\n";
    echo "  - Type: " . ($element['type'] ?? 'N/A') . "\n";
    echo "  - Propriétés disponibles: " . implode(', ', array_keys($element)) . "\n";
    echo "  - Texte/Content: '" . ($element['content'] ?? $element['text'] ?? 'N/A') . "'\n";
    echo "  - Position: x=" . ($element['x'] ?? 'N/A') . ", y=" . ($element['y'] ?? 'N/A') . "\n";
    echo "  - Dimensions: w=" . ($element['width'] ?? 'N/A') . ", h=" . ($element['height'] ?? 'N/A') . "\n";
    echo "  - Styles: color=" . ($element['color'] ?? 'N/A') . ", bg=" . ($element['backgroundColor'] ?? 'N/A') . "\n";
    echo "  - Bordure: " . ($element['borderWidth'] ?? 'N/A') . "px " . ($element['borderColor'] ?? 'N/A') . "\n";
    echo "  - Police: " . ($element['fontSize'] ?? 'N/A') . "px " . ($element['fontFamily'] ?? 'N/A') . " " . ($element['fontWeight'] ?? 'N/A') . "\n";
    echo "  - Alignement: " . ($element['textAlign'] ?? 'N/A') . "\n";
    echo "\n";
}

// Tester la génération
echo "=== TEST GÉNÉRATION PDF ===\n";
try {
    $pdf_content = $generator->generate_from_elements($test_elements);

    if ($pdf_content) {
        $filename = __DIR__ . '/test-react-elements.pdf';
        file_put_contents($filename, $pdf_content);
        echo "✅ PDF généré avec succès\n";
        echo "📁 Sauvegardé dans: $filename\n";
        echo "📊 Taille: " . strlen($pdf_content) . " octets\n";
    } else {
        echo "❌ Échec génération PDF\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>