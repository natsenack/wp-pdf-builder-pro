<?php
/**
 * Debug Autoloader - Diagnostic détaillé
 */

// Test 1: PHP et chemins
echo "<h1>🔍 Debug Autoloader Détaillé</h1>";
echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Test 2: Chemins du plugin
echo "<h2>1. 📁 Chemins du plugin</h2>";
$plugin_dir = dirname(__FILE__) . '/';
echo "Plugin dir: $plugin_dir<br>";
echo "Real path: " . realpath($plugin_dir) . "<br>";
echo "<hr>";

// Test 3: Autoloader - chargement manuel
echo "<h2>2. 🔄 Test autoloader manuel</h2>";

$autoloader_path = $plugin_dir . 'core/autoloader.php';
echo "Autoloader path: $autoloader_path<br>";
echo "Autoloader exists: " . (file_exists($autoloader_path) ? "✅ OUI" : "❌ NON") . "<br>";

if (file_exists($autoloader_path)) {
    echo "Tentative de chargement manuel...<br>";
    try {
        require_once $autoloader_path;
        echo "✅ Autoloader chargé sans erreur<br>";

        // Vérifier si la classe autoloader existe
        if (class_exists('PDF_Builder_Autoloader')) {
            echo "✅ Classe PDF_Builder_Autoloader existe<br>";

            // Inspecter les propriétés statiques
            $reflection = new ReflectionClass('PDF_Builder_Autoloader');
            $prefixes_prop = $reflection->getProperty('prefixes');
            $prefixes_prop->setAccessible(true);
            $prefixes = $prefixes_prop->getValue();

            echo "Mappings configurés:<br>";
            foreach ($prefixes as $prefix => $path) {
                echo "  - '$prefix' → '$path'<br>";
            }

            $base_path_prop = $reflection->getProperty('base_path');
            $base_path_prop->setAccessible(true);
            $base_path = $base_path_prop->getValue();
            echo "Base path: '$base_path'<br>";

        } else {
            echo "❌ Classe PDF_Builder_Autoloader n'existe pas<br>";
        }

    } catch (Exception $e) {
        echo "❌ Erreur chargement autoloader: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Fichier autoloader introuvable<br>";
}

echo "<hr>";

// Test 4: Test manuel de chargement de classe
echo "<h2>3. 📦 Test chargement manuel de classe</h2>";

$test_class = 'WP_PDF_Builder_Pro\\Data\\DataProviderInterface';
$test_file = $plugin_dir . 'data/DataProviderInterface.php';

echo "Classe test: $test_class<br>";
echo "Fichier attendu: $test_file<br>";
echo "Fichier existe: " . (file_exists($test_file) ? "✅ OUI" : "❌ NON") . "<br>";

if (file_exists($test_file)) {
    echo "Tentative de require_once manuel...<br>";
    try {
        require_once $test_file;
        echo "✅ Fichier chargé<br>";
        echo "Classe existe maintenant: " . (class_exists($test_class) ? "✅ OUI" : "❌ NON") . "<br>";
    } catch (Exception $e) {
        echo "❌ Erreur require_once: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";

// Test 5: Test autoload simulé
echo "<h2>4. 🔍 Simulation autoload</h2>";

if (class_exists('PDF_Builder_Autoloader')) {
    $test_classes = [
        'WP_PDF_Builder_Pro\\Data\\DataProviderInterface',
        'WP_PDF_Builder_Pro\\Data\\SampleDataProvider',
        'WP_PDF_Builder_Pro\\Generators\\BaseGenerator',
        'WP_PDF_Builder_Pro\\Generators\\PDFGenerator'
    ];

    foreach ($test_classes as $class) {
        echo "Test classe: $class<br>";

        // Simuler la logique d'autoload
        $prefixes = ['PDF_Builder\\' => 'src/', 'WP_PDF_Builder_Pro\\' => ''];
        $found = false;

        foreach ($prefixes as $prefix => $base_dir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) === 0) {
                $relative_class = substr($class, $len);
                $file = $plugin_dir . $base_dir . str_replace('\\', '/', $relative_class) . '.php';
                echo "  → Fichier calculé: $file<br>";
                echo "  → Fichier existe: " . (file_exists($file) ? "✅ OUI" : "❌ NON") . "<br>";

                if (file_exists($file)) {
                    $found = true;
                    // Tester le chargement
                    try {
                        require_once $file;
                        echo "  → Chargement: ✅ OK<br>";
                        echo "  → Classe existe: " . (class_exists($class) ? "✅ OUI" : "❌ NON") . "<br>";
                    } catch (Exception $e) {
                        echo "  → Erreur chargement: " . $e->getMessage() . "<br>";
                    }
                }
                break;
            }
        }

        if (!$found) {
            echo "  → Aucun mapping trouvé<br>";
        }

        echo "<br>";
    }
} else {
    echo "❌ PDF_Builder_Autoloader non disponible pour simulation<br>";
}

echo "<hr>";

// Test 6: spl_autoload_functions
echo "<h2>5. 📋 Fonctions autoload enregistrées</h2>";
$autoloaders = spl_autoload_functions();
if ($autoloaders) {
    echo "Autoloaders enregistrés:<br>";
    foreach ($autoloaders as $autoloader) {
        if (is_array($autoloader)) {
            echo "  - " . $autoloader[0] . "::" . $autoloader[1] . "<br>";
        } else {
            echo "  - " . $autoloader . "<br>";
        }
    }
} else {
    echo "Aucun autoloader enregistré<br>";
}

echo "<hr>";

// Test 7: Test final avec class_exists après tout
echo "<h2>6. 🎯 Test final des classes</h2>";
$final_test_classes = [
    'WP_PDF_Builder_Pro\\Data\\DataProviderInterface',
    'WP_PDF_Builder_Pro\\Data\\SampleDataProvider',
    'WP_PDF_Builder_Pro\\Generators\\BaseGenerator',
    'WP_PDF_Builder_Pro\\Generators\\PDFGenerator'
];

foreach ($final_test_classes as $class) {
    $exists = class_exists($class, false);
    echo ($exists ? "✅" : "❌") . " $class<br>";
}

echo "<hr>";
echo "<p><em>Debug terminé à " . date('H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">d:\wp-pdf-builder-pro\plugin\debug-autoloader.php