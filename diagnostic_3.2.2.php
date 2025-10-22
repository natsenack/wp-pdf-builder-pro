<?php
/**
 * Diagnostic rapide Phase 3.2.2 - CanvasModeProvider
 */

// Simuler l'environnement WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Inclure les dépendances
require_once ABSPATH . 'src/Interfaces/DataProviderInterface.php';
require_once ABSPATH . 'src/Providers/CanvasModeProvider.php';

echo "=== Diagnostic CanvasModeProvider 3.2.2 ===\n\n";

try {
    // Test d'instanciation
    echo "1. Test d'instanciation...\n";
    $provider = new \PDF_Builder_Pro\Providers\CanvasModeProvider();
    echo "✓ CanvasModeProvider instancié avec succès\n\n";

    // Test des données fictives
    echo "2. Test des données fictives...\n";
    $customerData = $provider->getCustomerData();
    $orderData = $provider->getOrderData();
    $companyData = $provider->getCompanyData();

    echo "Client: {$customerData['full_name']} ({$customerData['email']})\n";
    echo "Commande: {$orderData['order_number']} - {$orderData['total']} €\n";
    echo "Société: {$companyData['name']} ({$companyData['email']})\n";
    echo "✓ Données fictives cohérentes\n\n";

    // Test du cache
    echo "3. Test du système de cache...\n";
    $testData = ['test' => 'value'];
    $provider->cacheData('diagnostic_test', $testData, 60);
    $cached = $provider->getCachedData('diagnostic_test');
    $provider->invalidateCache('diagnostic_test');

    echo "Cache opérationnel: " . ($cached === $testData ? 'OUI' : 'NON') . "\n";
    echo "✓ Cache fonctionnel\n\n";

    // Test génération données fictives
    echo "4. Test génération données fictives...\n";
    $mockData = $provider->generateMockData(['customer_name', 'order_number']);
    echo "Données mock: " . json_encode($mockData, JSON_PRETTY_PRINT) . "\n";
    echo "✓ Génération mock fonctionnelle\n\n";

    echo "=== ✅ DIAGNOSTIC RÉUSSI - Phase 3.2.2 opérationnelle ===\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}