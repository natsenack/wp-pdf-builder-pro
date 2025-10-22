<?php
/**
 * Test rapide de la classe ShapeRenderer
 * Phase 3.3.3 - Test des formes géométriques
 */

// Simuler les constantes WordPress pour le test
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Inclure la classe
require_once __DIR__ . '/src/Renderers/ShapeRenderer.php';

echo "=== Test ShapeRenderer 3.3.3 ===\n\n";

try {
    // Test 1: Instanciation
    echo "Test 1: Instanciation\n";
    $renderer = new \PDF_Builder\Renderers\ShapeRenderer();
    echo "✓ Classe instanciée sans erreur\n";

    // Test 2: Vérification des types supportés
    echo "\nTest 2: Types supportés\n";
    $supportedTypes = $renderer->getSupportedTypes();
    echo "Types supportés: " . implode(', ', $supportedTypes) . "\n";
    echo "Nombre de types: " . count($supportedTypes) . "\n";

    // Test 3: Test de support de type
    echo "\nTest 3: Test de support de type\n";
    echo "Rectangle supporté: " . ($renderer->supports('rectangle') ? 'Oui' : 'Non') . "\n";
    echo "Cercle supporté: " . ($renderer->supports('circle') ? 'Oui' : 'Non') . "\n";
    echo "Ligne supportée: " . ($renderer->supports('line') ? 'Oui' : 'Non') . "\n";
    echo "Flèche supportée: " . ($renderer->supports('arrow') ? 'Oui' : 'Non') . "\n";
    echo "Type invalide supporté: " . ($renderer->supports('invalid') ? 'Oui' : 'Non') . "\n";

    // Test 4: Rendu d'un rectangle
    echo "\nTest 4: Rendu d'un rectangle\n";
    $rectangleElement = [
        'type' => 'rectangle',
        'x' => 10,
        'y' => 20,
        'width' => 100,
        'height' => 50,
        'properties' => [
            'fill' => '#ff0000',
            'stroke' => '#000000',
            'strokeWidth' => '2px',
            'borderRadius' => 5
        ]
    ];
    $rectangleHtml = $renderer->render($rectangleElement);
    echo "HTML généré (rectangle): " . (strpos($rectangleHtml, 'pdf-rectangle') !== false ? '✓' : '✗') . "\n";
    echo "Contient background-color: " . (strpos($rectangleHtml, 'background-color') !== false ? '✓' : '✗') . "\n";
    echo "Contient border-radius: " . (strpos($rectangleHtml, 'border-radius') !== false ? '✓' : '✗') . "\n";

    // Test 5: Rendu d'un cercle
    echo "\nTest 5: Rendu d'un cercle\n";
    $circleElement = [
        'type' => 'circle',
        'x' => 50,
        'y' => 50,
        'width' => 80,
        'height' => 80,
        'properties' => [
            'fill' => '#00ff00',
            'stroke' => '#000000',
            'strokeWidth' => '1px'
        ]
    ];
    $circleHtml = $renderer->render($circleElement);
    echo "HTML généré (cercle): " . (strpos($circleHtml, 'pdf-circle') !== false ? '✓' : '✗') . "\n";
    echo "Contient border-radius 50%: " . (strpos($circleHtml, 'border-radius: 50%') !== false ? '✓' : '✗') . "\n";

    // Test 6: Rendu d'une ligne
    echo "\nTest 6: Rendu d'une ligne\n";
    $lineElement = [
        'type' => 'line',
        'x' => 10,
        'y' => 100,
        'width' => 200,
        'height' => 1,
        'properties' => [
            'stroke' => '#0000ff',
            'strokeWidth' => '3px'
        ]
    ];
    $lineHtml = $renderer->render($lineElement);
    echo "HTML généré (ligne): " . (strpos($lineHtml, 'pdf-line') !== false ? '✓' : '✗') . "\n";
    echo "Contient border-top: " . (strpos($lineHtml, 'border-top') !== false ? '✓' : '✗') . "\n";

    // Test 7: Rendu d'une flèche
    echo "\nTest 7: Rendu d'une flèche\n";
    $arrowElement = [
        'type' => 'arrow',
        'x' => 20,
        'y' => 150,
        'width' => 100,
        'height' => 50,
        'properties' => [
            'direction' => 'right',
            'fill' => '#ffff00',
            'stroke' => '#000000',
            'strokeWidth' => 2
        ]
    ];
    $arrowHtml = $renderer->render($arrowElement);
    echo "HTML généré (flèche): " . (strpos($arrowHtml, 'pdf-arrow') !== false ? '✓' : '✗') . "\n";
    echo "Contient SVG: " . (strpos($arrowHtml, '<svg') !== false ? '✓' : '✗') . "\n";
    echo "Contient polygon: " . (strpos($arrowHtml, '<polygon') !== false ? '✓' : '✗') . "\n";

    // Test 8: Test d'erreur - élément invalide
    echo "\nTest 8: Élément invalide\n";
    $invalidElement = ['type' => 'invalid'];
    $errorHtml = $renderer->render($invalidElement);
    echo "HTML d'erreur généré: " . (strpos($errorHtml, 'pdf-shape-error') !== false ? '✓' : '✗') . "\n";

    // Test 9: Test d'erreur - type non supporté
    echo "\nTest 9: Type non supporté\n";
    $unsupportedElement = [
        'type' => 'triangle',
        'x' => 0,
        'y' => 0,
        'width' => 100,
        'height' => 100
    ];
    $unsupportedHtml = $renderer->render($unsupportedElement);
    echo "HTML d'erreur généré: " . (strpos($unsupportedHtml, 'Élément de forme invalide') !== false ? '✓' : '✗') . "\n";

    // Test 10: Validation d'élément
    echo "\nTest 10: Validation d'élément\n";
    $validElement = [
        'type' => 'rectangle',
        'x' => 10,
        'y' => 10,
        'width' => 50,
        'height' => 50
    ];
    $invalidElement2 = ['type' => 'rectangle']; // Manque x, y, width, height
    echo "Élément valide: " . ($renderer->supports('rectangle') ? '✓' : '✗') . "\n";
    echo "Élément invalide (manque propriétés): " . (!$renderer->supports('invalid') ? '✓' : '✗') . "\n";

    // Test 11: Dimensions minimales
    echo "\nTest 11: Dimensions minimales\n";
    $smallElement = [
        'type' => 'rectangle',
        'x' => 0,
        'y' => 0,
        'width' => 5,  // Plus petit que MIN_DIMENSIONS
        'height' => 5
    ];
    $smallHtml = $renderer->render($smallElement);
    echo "Dimensions forcées aux minima: " . (strpos($smallHtml, 'width: 10px') !== false ? '✓' : '✗') . "\n";

    // Test 12: Styles par défaut
    echo "\nTest 12: Styles par défaut\n";
    $defaultElement = [
        'type' => 'rectangle',
        'x' => 0,
        'y' => 0,
        'width' => 100,
        'height' => 100
    ];
    $defaultHtml = $renderer->render($defaultElement);
    echo "Styles par défaut appliqués: " . (strpos($defaultHtml, 'border: 1px solid #000000') !== false ? '✓' : '✗') . "\n";

    echo "\n=== Tests terminés avec succès ===\n";

} catch (Exception $e) {
    echo "\n❌ Erreur lors des tests: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}