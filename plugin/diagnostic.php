<?php

/**
 * Diagnostic des DataProviders et Interfaces
 * À exécuter sur le serveur pour vérifier l'absence de doublons
 */

echo "🔍 Diagnostic des Interfaces et DataProviders\n";
echo "==============================================\n\n";

// Test 1: Vérifier que l'interface se charge correctement
echo "1. Test de chargement de DataProviderInterface...\n";
try {
    require_once __DIR__ . '/interfaces/DataProviderInterface.php';
    echo "✅ DataProviderInterface chargée avec succès\n";

    // Vérifier que l'interface existe
    if (interface_exists('PDF_Builder\Interfaces\DataProviderInterface')) {
        echo "✅ Interface DataProviderInterface existe\n";
    } else {
        echo "❌ Interface DataProviderInterface n'existe pas\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur lors du chargement de l'interface: " . $e->getMessage() . "\n";
}

// Test 2: Vérifier les DataProviders
echo "\n2. Test de chargement des DataProviders...\n";
try {
    require_once __DIR__ . '/data/SampleDataProvider.php';
    require_once __DIR__ . '/data/WooCommerceDataProvider.php';

    echo "✅ DataProviders chargés avec succès\n";

    // Tester SampleDataProvider
    $sample = new PDF_Builder\Data\SampleDataProvider();
    $sampleValue = $sample->getVariableValue('customer_name');
    echo "✅ SampleDataProvider fonctionne: {$sampleValue}\n";

    // Tester WooCommerceDataProvider
    $woo = new PDF_Builder\Data\WooCommerceDataProvider();
    $wooValue = $woo->getVariableValue('customer_name');
    echo "✅ WooCommerceDataProvider fonctionne: {$wooValue}\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du chargement des DataProviders: " . $e->getMessage() . "\n";
}

// Test 3: Vérifier l'absence de doublons
echo "\n3. Vérification de l'absence de doublons...\n";

$interfaceFiles = glob(__DIR__ . '/interfaces/DataProviderInterface.php');
$srcInterfaceFiles = glob(__DIR__ . '/src/Interfaces/DataProviderInterface.php');

echo "Fichiers DataProviderInterface dans /interfaces: " . count($interfaceFiles) . "\n";
echo "Fichiers DataProviderInterface dans /src/Interfaces: " . count($srcInterfaceFiles) . "\n";

if (count($interfaceFiles) === 1 && count($srcInterfaceFiles) === 0) {
    echo "✅ Configuration correcte: 1 interface, 0 doublon\n";
} else {
    echo "❌ Configuration incorrecte: doublons détectés\n";
}

echo "\n🎯 Diagnostic terminé\n";

if (count($interfaceFiles) === 1 && count($srcInterfaceFiles) === 0) {
    echo "✅ Le problème de doublon d'interface est RÉSOLU !\n";
} else {
    echo "❌ Le problème persiste - vérifiez la configuration\n";
}