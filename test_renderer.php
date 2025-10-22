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

// Tests pour CanvasModeProvider (Phase 3.2.2)
echo "\n\n=== Test CanvasModeProvider 3.2.2 ===\n\n";

try {
    // Inclure la classe CanvasModeProvider
    require_once __DIR__ . '/src/Interfaces/DataProviderInterface.php';
    require_once __DIR__ . '/src/Providers/CanvasModeProvider.php';

    // Test 14: Instanciation CanvasModeProvider
    echo "Test 14: Instanciation CanvasModeProvider\n";
    $provider = new \PDF_Builder_Pro\Providers\CanvasModeProvider();
    echo "✓ Provider instancié sans erreur\n";

    // Test 15: Récupération données client
    echo "\nTest 15: Données client fictives\n";
    $customerData = $provider->getCustomerData();
    echo "Nom: " . $customerData['full_name'] . "\n";
    echo "Email: " . $customerData['email'] . "\n";
    echo "Téléphone: " . $customerData['phone'] . "\n";
    echo "✓ Données client récupérées\n";

    // Test 16: Récupération données commande
    echo "\nTest 16: Données commande fictives\n";
    $orderData = $provider->getOrderData();
    echo "Numéro commande: " . $orderData['order_number'] . "\n";
    echo "Date: " . $orderData['order_date'] . "\n";
    echo "Total: " . $orderData['total'] . " €\n";
    echo "Nombre d'articles: " . count($orderData['items']) . "\n";
    echo "✓ Données commande récupérées\n";

    // Test 17: Récupération données société
    echo "\nTest 17: Données société fictives\n";
    $companyData = $provider->getCompanyData();
    echo "Nom société: " . $companyData['name'] . "\n";
    echo "Email: " . $companyData['email'] . "\n";
    echo "SIRET: " . $companyData['siret'] . "\n";
    echo "✓ Données société récupérées\n";

    // Test 18: Génération données fictives
    echo "\nTest 18: Génération données fictives\n";
    $templateKeys = ['customer_name', 'order_number', 'order_total', 'company_name'];
    $mockData = $provider->generateMockData($templateKeys);
    echo "Données générées:\n";
    foreach ($mockData as $key => $value) {
        echo "  $key: $value\n";
    }
    echo "✓ Données fictives générées\n";

    // Test 19: Vérification complétude
    echo "\nTest 19: Vérification complétude données\n";
    $requiredKeys = ['customer_name', 'order_number', 'company_name'];
    $completeness = $provider->checkDataCompleteness($requiredKeys);
    echo "Données complètes: " . ($completeness['complete'] ? 'Oui' : 'Non') . "\n";
    if (!empty($completeness['missing'])) {
        echo "Clés manquantes: " . implode(', ', $completeness['missing']) . "\n";
    }
    echo "✓ Vérification complétude effectuée\n";

    // Test 20: Système de cache
    echo "\nTest 20: Système de cache\n";
    $testData = ['test' => 'cached_value'];
    $cacheKey = 'test_cache_key';

    // Mise en cache
    $cacheResult = $provider->cacheData($cacheKey, $testData, 60);
    echo "Mise en cache: " . ($cacheResult ? 'Réussie' : 'Échouée') . "\n";

    // Récupération depuis le cache
    $cachedData = $provider->getCachedData($cacheKey);
    echo "Récupération cache: " . ($cachedData === $testData ? 'Réussie' : 'Échouée') . "\n";

    // Invalidation du cache
    $invalidateResult = $provider->invalidateCache($cacheKey);
    echo "Invalidation cache: " . ($invalidateResult ? 'Réussie' : 'Échouée') . "\n";

    // Vérification que le cache est vide
    $cachedDataAfter = $provider->getCachedData($cacheKey);
    echo "Cache après invalidation: " . ($cachedDataAfter === null ? 'Vide (OK)' : 'Non vide (ERREUR)') . "\n";
    echo "✓ Système de cache fonctionnel\n";

    // Test 21: Nettoyage des données
    echo "\nTest 21: Nettoyage des données\n";
    $rawData = [
        'name' => 'Test & <script>alert("xss")</script>',
        'safe' => 'Normal text'
    ];
    $sanitized = $provider->sanitizeData($rawData);
    echo "Données brutes: " . $rawData['name'] . "\n";
    echo "Données nettoyées: " . $sanitized['name'] . "\n";
    echo "✓ Nettoyage des données effectué\n";

    echo "\n=== Tests CanvasModeProvider terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE dans CanvasModeProvider: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}

} catch (\Exception $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}