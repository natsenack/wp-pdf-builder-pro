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

    // Test 12: Zoom
    echo "\nTest 12: Zoom\n";
    echo "Zoom initial: " . $renderer->getZoom() . "%\n";
    echo "Niveaux autorisés: " . implode(', ', $renderer->getAllowedZoomLevels()) . "\n";

    $zoomSet = $renderer->setZoom(125);
    echo "setZoom(125): " . ($zoomSet ? 'Réussi' : 'Échoué') . "\n";
    echo "Zoom après setZoom(125): " . $renderer->getZoom() . "%\n";

    $zoomIn = $renderer->zoomIn();
    echo "zoomIn(): " . ($zoomIn ? 'Réussi' : 'Échoué') . "\n";
    echo "Zoom après zoomIn(): " . $renderer->getZoom() . "%\n";

    $zoomOut = $renderer->zoomOut();
    echo "zoomOut(): " . ($zoomOut ? 'Réussi' : 'Échoué') . "\n";
    echo "Zoom après zoomOut(): " . $renderer->getZoom() . "%\n";

    // Test 13: Responsive
    echo "\nTest 13: Responsive\n";
    echo "Responsive initial: " . ($renderer->isResponsive() ? 'Activé' : 'Désactivé') . "\n";

    $responsiveSet = $renderer->setResponsive(false);
    echo "setResponsive(false): " . ($responsiveSet ? 'Réussi' : 'Échoué') . "\n";
    echo "Responsive après setResponsive(false): " . ($renderer->isResponsive() ? 'Activé' : 'Désactivé') . "\n";

    $containerSet = $renderer->setContainerDimensions(800, 600);
    echo "setContainerDimensions(800, 600): " . ($containerSet ? 'Réussi' : 'Échoué') . "\n";

    $responsiveDims = $renderer->getResponsiveDimensions();
    echo "getResponsiveDimensions(): " . ($responsiveDims ? "Dimensions calculées" : "Null (pas responsive)") . "\n";

    $scrollbars = $renderer->getScrollbarState();
    echo "getScrollbarState(): Horizontal=" . ($scrollbars['horizontal'] ? 'Oui' : 'Non') . ", Vertical=" . ($scrollbars['vertical'] ? 'Oui' : 'Non') . "\n";

    // Test 14: Rendu d'élément texte
    echo "\nTest 14: Rendu d'élément texte\n";
    $renderer = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas']);
    $renderer->init();
    $elementData = [
        'type' => 'text',
        'text' => 'Hello World',
        'x' => 10,
        'y' => 20,
        'width' => 100,
        'height' => 50,
        'fontSize' => 14,
        'color' => '#ff0000',
        'bold' => true
    ];
    $result = $renderer->renderElement($elementData);
    echo "Rendu élément texte: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if ($result['success']) {
        echo "HTML généré: " . substr($result['html'], 0, 50) . "...\n";
        echo "Position: x=" . $result['x'] . ", y=" . $result['y'] . "\n";
    }

    // Test 15: Rendu d'élément rectangle
    echo "\nTest 15: Rendu d'élément rectangle\n";
    $elementData = [
        'type' => 'rectangle',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 100,
        'fillColor' => '#00ff00',
        'borderWidth' => 2,
        'borderColor' => '#000000'
    ];
    $result = $renderer->renderElement($elementData);
    echo "Rendu élément rectangle: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";

    // Test 16: Rendu avec zoom
    echo "\nTest 16: Rendu avec zoom\n";
    $rendererZoom = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas', 'zoom' => 150]);
    $rendererZoom->init();
    $elementData = [
        'type' => 'text',
        'text' => 'Zoom Test',
        'x' => 10,
        'y' => 10,
        'width' => 100,
        'height' => 30
    ];
    $result = $rendererZoom->renderElement($elementData);
    echo "Rendu avec zoom 150%: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if ($result['success']) {
        echo "Zoom appliqué: " . $result['zoom_applied'] . "%\n";
        echo "Dimensions ajustées: " . $result['width'] . "x" . $result['height'] . "\n";
    }

    // Test 17: Rendu responsive
    echo "\nTest 17: Rendu responsive\n";
    $rendererResponsive = new \PDF_Builder\Renderers\PreviewRenderer(['mode' => 'canvas', 'responsive' => true]);
    $rendererResponsive->init();
    $rendererResponsive->setContainerDimensions(400, 300);
    $result = $rendererResponsive->renderElement($elementData);
    echo "Rendu responsive: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if ($result['success']) {
        echo "Responsive appliqué: " . ($result['responsive_applied'] ? "Oui" : "Non") . "\n";
    }

    // Test 18: Élément non supporté
    echo "\nTest 18: Élément non supporté\n";
    $elementData = [
        'type' => 'unsupported_element',
        'x' => 0,
        'y' => 0,
        'width' => 100,
        'height' => 100
    ];
    $result = $renderer->renderElement($elementData);
    echo "Rendu élément non supporté: " . ($result['success'] ? "Réussi" : "Échoué") . "\n";
    if (!$result['success']) {
        echo "Erreur attendue: " . $result['error'] . "\n";
    }

    echo "\n=== Tous les tests terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}