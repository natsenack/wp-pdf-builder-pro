<?php
/**
 * Test direct du chargement des classes avec bootstrap minimal
 * Charge le bootstrap directement pour tester les classes
 */

// Simuler les constantes WordPress nécessaires
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', dirname(dirname(__FILE__)));
}

// Définir les constantes du plugin nécessaires
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
}
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', dirname(__FILE__) . '/pdf-builder-pro.php');
}

echo "=== TEST DIRECT CHARGEMENT CLASSES ===\n\n";

// Test 0: Vérifier que le bootstrap minimal existe
echo "0. Bootstrap minimal existe: ";
$bootstrap_path = __DIR__ . '/bootstrap-minimal.php';
if (file_exists($bootstrap_path)) {
    echo "✅ OK\n";
} else {
    echo "❌ NON (" . $bootstrap_path . ")\n";
    exit(1);
}

// Test 1: Charger le bootstrap minimal
echo "1. Chargement bootstrap minimal: ";
try {
    require_once $bootstrap_path;
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Vérifier que la fonction d'initialisation existe
echo "2. Fonction pdf_builder_load_bootstrap existe: ";
if (function_exists('pdf_builder_load_bootstrap')) {
    echo "✅ OK\n";
} else {
    echo "❌ NON\n";
    exit(1);
}

// Test 3: Exécuter l'initialisation
echo "3. Exécution pdf_builder_load_bootstrap: ";
try {
    pdf_builder_load_bootstrap();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Vérifier que l'autoloader est chargé
echo "4. Autoloader chargé: ";
if (class_exists('PDF_Builder_Autoloader')) {
    echo "✅ OK\n";

    // Debug: vérifier que l'autoloader est enregistré
    echo "4b. Autoloader SPL enregistré: ";
    $autoloader_functions = spl_autoload_functions();
    $found = false;
    foreach ($autoloader_functions as $func) {
        if (is_array($func) && isset($func[0]) && $func[0] === 'PDF_Builder_Autoloader') {
            $found = true;
            break;
        }
    }
    echo $found ? "✅ OK\n" : "❌ NON\n";

    // Debug: tester manuellement l'autoloader
    echo "4c. Test manuel autoloader pour SampleDataProvider: ";
    try {
        $result = PDF_Builder_Autoloader::autoload('WP_PDF_Builder_Pro\\Data\\SampleDataProvider');
        echo $result ? "✅ OK\n" : "❌ NON\n";
    } catch (Exception $e) {
        echo "❌ ERREUR: " . $e->getMessage() . "\n";
    }

} else {
    echo "❌ NON\n";
}

// Test 5: Tester le chargement d'une classe WP_PDF_Builder_Pro
echo "5. Classe WP_PDF_Builder_Pro\\DataProviderInterface: ";
$class_name = 'WP_PDF_Builder_Pro\\Data\\DataProviderInterface';
echo "Test de $class_name: ";
try {
    // Debug: vérifier le fichier attendu
    $expected_file = PDF_BUILDER_PLUGIN_DIR . 'data/DataProviderInterface.php';
    echo "Fichier attendu: $expected_file - ";
    echo file_exists($expected_file) ? "EXISTS" : "NOT FOUND";

    if (interface_exists($class_name)) {
        echo " ✅ OK\n";
    } else {
        echo " ❌ NON\n";
    }
} catch (Exception $e) {
    echo " ❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 6: Tester le chargement d'une implémentation
echo "6. Classe WP_PDF_Builder_Pro\\Data\\SampleDataProvider: ";
try {
    if (class_exists('WP_PDF_Builder_Pro\\Data\\SampleDataProvider')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 7: Tester le chargement du générateur PDF
echo "7. Classe WP_PDF_Builder_Pro\\Generators\\PDFGenerator: ";
try {
    if (class_exists('WP_PDF_Builder_Pro\\Generators\\PDFGenerator')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 8: Tester l'instanciation SampleDataProvider
echo "8. Instanciation SampleDataProvider: ";
try {
    $provider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 9: Tester l'instanciation du générateur PDF
echo "9. Instanciation PDFGenerator: ";
try {
    $template_data = ['test' => 'data'];
    $data_provider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();
    $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $data_provider);
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 10: Tester une génération PDF basique
echo "10. Test génération PDF basique: ";
try {
    $generator = new WP_PDF_Builder_Pro\PDFGenerator();
    $provider = new WP_PDF_Builder_Pro\SampleDataProvider();

    // HTML simple pour test
    $html = '<html><body><h1>Test PDF</h1><p>Ceci est un test.</p></body></html>';

    // Générer le PDF
    $pdf_content = $generator->generatePDF($html, 'test.pdf');

    if (!empty($pdf_content)) {
        echo "✅ OK (" . strlen($pdf_content) . " bytes)\n";
    } else {
        echo "❌ PDF vide\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST DIRECT ===\n";
echo "Si tous les tests sont OK, l'architecture fonctionne correctement !\n";
?>