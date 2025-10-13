<?php
// Test de débogage de l'aperçu
echo "<h1>Débogage de l'Aperçu PDF</h1>";

// Tester les éléments qui sont passés à l'aperçu
$test_elements = [
    [
        'id' => 'text-1',
        'type' => 'text',
        'content' => 'Hello World',
        'text' => 'Hello World',
        'x' => 100,
        'y' => 100,
        'width' => 150,
        'height' => 40,
        'fontSize' => 14,
        'color' => '#333333',
        'fontFamily' => 'Arial, sans-serif',
        'fontWeight' => 'normal',
        'textAlign' => 'left'
    ],
    [
        'id' => 'rect-1',
        'type' => 'rectangle',
        'x' => 50,
        'y' => 200,
        'width' => 200,
        'height' => 100,
        'borderColor' => '#ff0000',
        'borderWidth' => 2,
        'backgroundColor' => 'transparent'
    ]
];

echo "<h2>Éléments de test :</h2>";
echo "<pre>" . json_encode($test_elements, JSON_PRETTY_PRINT) . "</pre>";

// Tester la génération PDF avec ces éléments
define('PDF_GENERATOR_TEST_MODE', true);
require_once 'includes/pdf-generator.php';

$generator = new PDF_Builder_Pro_Generator();
$pdf = $generator->generate($test_elements);

echo "<h2>Test génération PDF :</h2>";
if ($pdf) {
    echo "<p style='color: green;'>✅ PDF généré avec succès (" . strlen($pdf) . " octets)</p>";
} else {
    echo "<p style='color: red;'>❌ Erreur génération PDF</p>";
    $errors = $generator->get_errors();
    echo "<pre>Erreurs: " . json_encode($errors) . "</pre>";
}

echo "<h2>Analyse du problème d'aperçu :</h2>";
echo "<p>Si le PDF se génère correctement mais que l'aperçu ne s'affiche pas, le problème est probablement :</p>";
echo "<ul>";
echo "<li>1. Les éléments n'ont pas les bonnes propriétés pour le rendu React</li>";
echo "<li>2. Le composant CanvasElement ne gère pas correctement le mode aperçu</li>";
echo "<li>3. Les styles CSS ne sont pas appliqués correctement</li>";
echo "<li>4. Il y a une erreur JavaScript dans la console</li>";
echo "</ul>";

echo "<h2>Propriétés attendues pour les éléments :</h2>";
echo "<pre>";
echo "Text element: {
  id: string,
  type: 'text',
  content: string,  // ou text: string
  x: number,
  y: number,
  width: number,
  height: number,
  fontSize: number,
  color: string,
  fontFamily?: string,
  fontWeight?: string,
  textAlign?: string
}

Rectangle element: {
  id: string,
  type: 'rectangle',
  x: number,
  y: number,
  width: number,
  height: number,
  borderColor?: string,
  borderWidth?: number,
  backgroundColor?: string
}";
echo "</pre>";

echo "<h2>Actions recommandées :</h2>";
echo "<ol>";
echo "<li>Vérifier la console JavaScript pour les erreurs</li>";
echo "<li>Vérifier que les éléments ont les bonnes propriétés</li>";
echo "<li>Tester avec des éléments très simples</li>";
echo "<li>Vérifier que les composants React se montent correctement</li>";
echo "</ol>";
?>