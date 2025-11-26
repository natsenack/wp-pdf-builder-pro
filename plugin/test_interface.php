<?php
/**
 * Test rapide de l'interface DataProviderInterface
 * Accessible via navigateur: http://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/test_interface.php
 */

header('Content-Type: text/plain; charset=utf-8');
echo "=== TEST RAPIDE DE L'INTERFACE DataProviderInterface ===\n\n";

try {
    // Test de chargement de l'interface
    echo "1. Chargement de l'interface...\n";
    require_once __DIR__ . '/interfaces/DataProviderInterface.php';
    echo "   ✅ Interface DataProviderInterface chargée avec succès\n\n";

    // Test de chargement des DataProviders
    echo "2. Chargement des DataProviders...\n";
    require_once __DIR__ . '/data/SampleDataProvider.php';
    echo "   ✅ SampleDataProvider chargé\n";

    require_once __DIR__ . '/data/WooCommerceDataProvider.php';
    echo "   ✅ WooCommerceDataProvider chargé\n\n";

    // Test d'instanciation
    echo "3. Test d'instanciation...\n";
    $sampleProvider = new PDF_Builder\Data\SampleDataProvider();
    echo "   ✅ SampleDataProvider instancié\n";

    $wooProvider = new PDF_Builder\Data\WooCommerceDataProvider();
    echo "   ✅ WooCommerceDataProvider instancié\n\n";

    // Test des méthodes de l'interface
    echo "4. Test des méthodes de l'interface...\n";

    // Test SampleDataProvider
    if ($sampleProvider->hasVariable('test_var')) {
        $value = $sampleProvider->getVariableValue('test_var');
        echo "   ✅ SampleDataProvider: variable 'test_var' = '$value'\n";
    }

    $allVars = $sampleProvider->getAllVariables();
    echo "   ✅ SampleDataProvider: " . count($allVars) . " variables disponibles\n";

    // Test WooCommerceDataProvider (si WooCommerce est actif)
    if (class_exists('WooCommerce')) {
        echo "   ✅ WooCommerce détecté - DataProvider prêt pour les variables WooCommerce\n";
    } else {
        echo "   ℹ️ WooCommerce non détecté - DataProvider configuré mais WooCommerce pas actif\n";
    }

    echo "\n🎉 TOUS LES TESTS RÉUSSIS !\n";
    echo "   Le système d'injection de données pour l'aperçu PDF est opérationnel.\n";

} catch (Throwable $e) {
    echo "❌ ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "📍 Fichier: " . $e->getFile() . " (ligne " . $e->getLine() . ")\n";
    echo "🔍 Trace:\n" . $e->getTraceAsString() . "\n";

    // Diagnostic supplémentaire pour les erreurs de déclaration dupliquée
    if (strpos($e->getMessage(), 'Cannot declare interface') !== false) {
        echo "\n🔧 DIAGNOSTIC: Erreur de déclaration dupliquée détectée\n";
        echo "   Solution: Vérifier qu'il n'y a qu'un seul fichier DataProviderInterface.php\n";
        echo "   Localisation attendue: interfaces/DataProviderInterface.php\n";
    }
}

echo "\n=== FIN DU TEST ===\n";