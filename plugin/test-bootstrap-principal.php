<?php
/**
 * Test du bootstrap principal corrigé
 * Teste le chargement avec bootstrap.php au lieu de bootstrap-minimal.php
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

echo "=== TEST BOOTSTRAP PRINCIPAL ===\n\n";

// Test 0: Vérifier que le bootstrap principal existe
echo "0. Bootstrap principal existe: ";
$bootstrap_path = __DIR__ . '/bootstrap.php';
if (file_exists($bootstrap_path)) {
    echo "✅ OK\n";
} else {
    echo "❌ NON (" . $bootstrap_path . ")\n";
    exit(1);
}

// Test 1: Charger le bootstrap principal
echo "1. Chargement bootstrap principal: ";
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
} else {
    echo "❌ NON\n";
}

// Test 5: Tester le chargement d'une classe WP_PDF_Builder_Pro
echo "5. Classe WP_PDF_Builder_Pro\\Data\\DataProviderInterface: ";
try {
    if (interface_exists('WP_PDF_Builder_Pro\\Data\\DataProviderInterface')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
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
    $template_data = [
        'template' => [
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'Test PDF Generation'
                ]
            ]
        ]
    ];
    $data_provider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();
    $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $data_provider);
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST BOOTSTRAP PRINCIPAL ===\n";
echo "Si tous les tests sont OK, le bootstrap principal fonctionne !\n";
?>