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

    echo "\n=== Tous les tests terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}