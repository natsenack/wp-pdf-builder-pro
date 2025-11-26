<?php
// Test rapide de l'interface DataProviderInterface
echo "=== TEST RAPIDE DE L'INTERFACE ===\n";

try {
    // Test de chargement de l'interface
    require_once 'interfaces/DataProviderInterface.php';
    echo "✅ Interface DataProviderInterface chargée avec succès\n";

    // Test de chargement des DataProviders
    require_once 'data/providers/SampleDataProvider.php';
    echo "✅ SampleDataProvider chargé\n";

    require_once 'data/providers/WooCommerceDataProvider.php';
    echo "✅ WooCommerceDataProvider chargé\n";

    // Test d'instanciation
    $sampleProvider = new PDF_Builder\Data\SampleDataProvider();
    echo "✅ SampleDataProvider instancié\n";

    $wooProvider = new PDF_Builder\Data\WooCommerceDataProvider();
    echo "✅ WooCommerceDataProvider instancié\n";

    echo "\n🎉 TOUS LES TESTS RÉUSSIS ! Le système est fonctionnel.\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "📍 Fichier: " . $e->getFile() . " (ligne " . $e->getLine() . ")\n";
}
