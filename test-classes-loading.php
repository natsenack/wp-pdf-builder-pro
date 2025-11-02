<?php
/**
 * Test rapide du chargement des classes avec bootstrap minimal
 * À exécuter sur le serveur pour vérifier que l'autoloader fonctionne
 */

echo "=== TEST RAPIDE CHARGEMENT CLASSES ===\n\n";

// Test 1: Vérifier que le plugin est activé
// Test 1: Vérifier que le plugin est activé
echo "1. Plugin activé: ";
$version = defined('WP_PDF_BUILDER_PRO_VERSION') ? WP_PDF_BUILDER_PRO_VERSION : 'inconnue';
if (defined('WP_PDF_BUILDER_PRO_VERSION')) {
    echo "✅ OK (v" . $version . ")\n";
} else {
    echo "❌ NON\n";
}

// Test 2: Vérifier que l'autoloader est chargé
echo "2. Autoloader chargé: ";
if (class_exists('PDF_Builder_Autoloader')) {
    echo "✅ OK\n";
} else {
    echo "❌ NON\n";
}

// Test 3: Tester le chargement d'une classe WP_PDF_Builder_Pro
echo "3. Classe WP_PDF_Builder_Pro\\DataProviderInterface: ";
try {
    if (interface_exists('WP_PDF_Builder_Pro\\DataProviderInterface')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 4: Tester le chargement d'une implémentation
echo "4. Classe WP_PDF_Builder_Pro\\SampleDataProvider: ";
try {
    if (class_exists('WP_PDF_Builder_Pro\\SampleDataProvider')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 5: Tester le chargement du générateur PDF
echo "5. Classe WP_PDF_Builder_Pro\\PDFGenerator: ";
try {
    if (class_exists('WP_PDF_Builder_Pro\\PDFGenerator')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 6: Tester l'instanciation basique
echo "6. Instanciation SampleDataProvider: ";
try {
    $provider = new WP_PDF_Builder_Pro\SampleDataProvider();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 7: Tester l'instanciation du générateur PDF
echo "7. Instanciation PDFGenerator: ";
try {
    $generator = new WP_PDF_Builder_Pro\PDFGenerator();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
echo "Si tous les tests sont OK, vous pouvez passer aux tests fonctionnels.\n";
?>