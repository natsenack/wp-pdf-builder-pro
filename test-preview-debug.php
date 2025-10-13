<?php
// Test de l'aperçu PDF
echo "<h1>Test de l'Aperçu PDF</h1>";

// Simuler des éléments de test
$test_elements = [
    [
        'id' => 'test-text-1',
        'type' => 'text',
        'content' => 'Test Aperçu PDF',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30,
        'fontSize' => 16,
        'color' => '#000000'
    ],
    [
        'id' => 'test-rect-1',
        'type' => 'rectangle',
        'x' => 40,
        'y' => 40,
        'width' => 220,
        'height' => 50,
        'borderColor' => '#ff0000',
        'borderWidth' => 2
    ]
];

echo "<h2>Éléments de test :</h2>";
echo "<pre>" . json_encode($test_elements, JSON_PRETTY_PRINT) . "</pre>";

// Tester la génération PDF
define('PDF_GENERATOR_TEST_MODE', true);
require_once 'includes/pdf-generator.php';

$generator = new PDF_Builder_Pro_Generator();
$pdf = $generator->generate($test_elements);

echo "<h2>Résultat de génération :</h2>";
if ($pdf) {
    echo "<p style='color: green;'>✅ PDF généré avec succès (" . strlen($pdf) . " octets)</p>";
    echo "<a href='data:application/pdf;base64," . base64_encode($pdf) . "' target='_blank'>📄 Ouvrir le PDF de test</a>";
} else {
    echo "<p style='color: red;'>❌ Erreur de génération PDF</p>";
    $errors = $generator->get_errors();
    echo "<pre>Erreurs: " . json_encode($errors, JSON_PRETTY_PRINT) . "</pre>";
}

echo "<h2>Métriques de performance :</h2>";
echo "<pre>" . json_encode($generator->get_performance_metrics(), JSON_PRETTY_PRINT) . "</pre>";
?>