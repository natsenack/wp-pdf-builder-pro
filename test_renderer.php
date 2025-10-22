<?php
/**
 * Test rapide de la classe PreviewRenderer
 * À exécuter pour vérifier l'étape 3.1.1
 */

// Simuler les constantes WordPress pour le test
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Inclure la classe (dans un environnement réel, utiliser l'autoloader)
require_once __DIR__ . '/src/Renderers/PreviewRenderer.php';

echo "=== Test PreviewRenderer 3.1.1 ===\n\n";

try {
    // Test 1: Instanciation normale
    echo "Test 1: Instanciation normale\n";
    $renderer = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas']);
    echo "✓ Classe instanciée sans erreur\n";

    // Test 2: Vérification des propriétés
    echo "\nTest 2: Vérification des propriétés\n";
    echo "Mode: " . $renderer->getMode() . "\n";
    $dimensions = $renderer->getDimensions();
    echo "Dimensions: {$dimensions['width']}x{$dimensions['height']}\n";
    echo "Initialisé: " . ($renderer->isInitialized() ? 'Oui' : 'Non') . "\n";

    // Test 3: Initialisation
    echo "\nTest 3: Initialisation\n";
    $initResult = $renderer->init();
    echo "Initialisation: " . ($initResult ? 'Réussie' : 'Échouée') . "\n";
    echo "État après init: " . ($renderer->isInitialized() ? 'Initialisé' : 'Non initialisé') . "\n";

    // Test 4: Test de rendu (basique)
    echo "\nTest 4: Test de rendu basique\n";
    $elementData = ['type' => 'text', 'content' => 'Test'];
    $renderResult = $renderer->render($elementData);
    echo "Rendu: " . ($renderResult ? 'Réussi' : 'Échoué') . "\n";

    // Test 5: Destruction
    echo "\nTest 5: Destruction\n";
    $destroyResult = $renderer->destroy();
    echo "Destruction: " . ($destroyResult ? 'Réussie' : 'Échouée') . "\n";

    // Test 6: Mode invalide
    echo "\nTest 6: Mode invalide\n";
    try {
        $invalidRenderer = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'invalid']);
        echo "✗ Mode invalide accepté (erreur attendue)\n";
    } catch (\Exception $e) {
        echo "✓ Mode invalide rejeté: " . $e->getMessage() . "\n";
    }

    // Test 7: Constantes A4
    echo "\nTest 7: Constantes A4\n";
    echo "A4_WIDTH_MM: " . \PDF_Builder\Renderers\PreviewRenderer::A4_WIDTH_MM . "mm\n";
    echo "A4_HEIGHT_MM: " . \PDF_Builder\Renderers\PreviewRenderer::A4_HEIGHT_MM . "mm\n";
    echo "A4_DPI: " . \PDF_Builder\Renderers\PreviewRenderer::A4_DPI . "\n";
    echo "A4_WIDTH_PX: " . \PDF_Builder\Renderers\PreviewRenderer::A4_WIDTH_PX . "px\n";
    echo "A4_HEIGHT_PX: " . \PDF_Builder\Renderers\PreviewRenderer::A4_HEIGHT_PX . "px\n";

    // Test 8: setDimensions()
    echo "\nTest 8: setDimensions()\n";
    $setResult = $renderer->setDimensions(1000, 1500);
    echo "setDimensions(1000, 1500): " . ($setResult ? 'Réussi' : 'Échoué') . "\n";
    $newDimensions = $renderer->getDimensions();
    echo "Nouvelles dimensions: {$newDimensions['width']}x{$newDimensions['height']}\n";

    // Test 9: resetToA4()
    echo "\nTest 9: resetToA4()\n";
    $resetResult = $renderer->resetToA4();
    echo "resetToA4(): " . ($resetResult ? 'Réussi' : 'Échoué') . "\n";
    $a4Dimensions = $renderer->getDimensions();
    echo "Dimensions A4: {$a4Dimensions['width']}x{$a4Dimensions['height']}\n";
    $isA4Correct = ($a4Dimensions['width'] === 794 && $a4Dimensions['height'] === 1123);
    echo "Dimensions A4 correctes: " . ($isA4Correct ? 'Oui' : 'Non') . "\n";

    // Test 10: calculatePixelDimensions()
    echo "\nTest 10: calculatePixelDimensions()\n";
    $calculated = \PDF_Builder\Renderers\PreviewRenderer::calculatePixelDimensions(210, 297, 150);
    echo "calculatePixelDimensions(210, 297, 150): {$calculated['width']}x{$calculated['height']}\n";
    $calcCorrect = ($calculated['width'] === 794 && $calculated['height'] === 1123);
    echo "Calcul correct: " . ($calcCorrect ? 'Oui' : 'Non') . "\n";

    // Test 11: Validation des dimensions
    echo "\nTest 11: Validation des dimensions\n";
    $invalidSet = $renderer->setDimensions(-100, 100);
    echo "setDimensions(-100, 100): " . ($invalidSet ? 'Accepté (erreur)' : 'Rejeté (correct)') . "\n";

    $tooBigSet = $renderer->setDimensions(6000, 6000);
    echo "setDimensions(6000, 6000): " . ($tooBigSet ? 'Accepté (erreur)' : 'Rejeté (correct)') . "\n";

    echo "\n=== Tous les tests terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}