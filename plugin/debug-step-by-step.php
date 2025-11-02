<?php
/**
 * Debug Étape par Étape - Test minimal
 */

echo "<h1>🔬 Debug Étape par Étape</h1>";
echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Étape 1: Test PHP de base
echo "<h2>Étape 1: PHP de base</h2>";
echo "✅ PHP fonctionne<br>";
echo "Version: " . PHP_VERSION . "<br>";
echo "<hr>";

// Étape 2: Test des chemins
echo "<h2>Étape 2: Chemins</h2>";
$plugin_dir = dirname(__FILE__) . '/';
echo "Plugin dir: $plugin_dir<br>";
echo "Current dir: " . __DIR__ . "<br>";
echo "<hr>";

// Étape 3: Test fichier autoloader
echo "<h2>Étape 3: Fichier autoloader</h2>";
$autoloader_file = $plugin_dir . 'core/autoloader.php';
echo "Fichier: $autoloader_file<br>";
echo "Existe: " . (file_exists($autoloader_file) ? "✅ OUI" : "❌ NON") . "<br>";

if (file_exists($autoloader_file)) {
    echo "Taille: " . filesize($autoloader_file) . " bytes<br>";
    echo "Modifié: " . date('Y-m-d H:i:s', filemtime($autoloader_file)) . "<br>";
}
echo "<hr>";

// Étape 4: Test contenu autoloader (sans l'exécuter)
echo "<h2>Étape 4: Contenu autoloader</h2>";
if (file_exists($autoloader_file)) {
    $content = file_get_contents($autoloader_file);
    echo "Contenu chargé: ✅<br>";
    echo "Taille contenu: " . strlen($content) . " caractères<br>";

    // Chercher les mappings
    if (strpos($content, 'WP_PDF_Builder_Pro') !== false) {
        echo "Mapping WP_PDF_Builder_Pro trouvé: ✅<br>";
    } else {
        echo "Mapping WP_PDF_Builder_Pro absent: ❌<br>";
    }

    if (strpos($content, "'WP_PDF_Builder_Pro\\\\' => ''") !== false) {
        echo "Mapping correct trouvé: ✅<br>";
    } else {
        echo "Mapping correct absent: ❌<br>";
    }
} else {
    echo "Contenu non testable: ❌<br>";
}
echo "<hr>";

// Étape 5: Test chargement autoloader (avec try/catch détaillé)
echo "<h2>Étape 5: Chargement autoloader</h2>";
if (file_exists($autoloader_file)) {
    echo "Tentative de chargement...<br>";

    try {
        // Test 1: syntaxe PHP
        $syntax_check = shell_exec("php -l \"$autoloader_file\" 2>&1");
        if (strpos($syntax_check, 'No syntax errors') !== false) {
            echo "✅ Syntaxe PHP OK<br>";
        } else {
            echo "❌ Erreur syntaxe: $syntax_check<br>";
        }

        // Test 2: require_once
        echo "Test require_once...<br>";
        require_once $autoloader_file;
        echo "✅ Autoloader chargé<br>";

        // Test 3: classe existe
        if (class_exists('PDF_Builder_Autoloader')) {
            echo "✅ Classe PDF_Builder_Autoloader existe<br>";
        } else {
            echo "❌ Classe PDF_Builder_Autoloader absente<br>";
        }

    } catch (Throwable $e) {
        echo "❌ ERREUR FATALE: " . $e->getMessage() . "<br>";
        echo "Fichier: " . $e->getFile() . "<br>";
        echo "Ligne: " . $e->getLine() . "<br>";
        echo "Trace:<br><pre>" . $e->getTraceAsString() . "</pre><br>";
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "<br>";
        echo "Fichier: " . $e->getFile() . "<br>";
        echo "Ligne: " . $e->getLine() . "<br>";
    }
} else {
    echo "Chargement impossible: fichier absent<br>";
}
echo "<hr>";

// Étape 6: Test classe simple (sans autoloader)
echo "<h2>Étape 6: Test classe simple</h2>";
$test_file = $plugin_dir . 'data/DataProviderInterface.php';
echo "Fichier test: $test_file<br>";
echo "Existe: " . (file_exists($test_file) ? "✅ OUI" : "❌ NON") . "<br>";

if (file_exists($test_file)) {
    try {
        echo "Chargement manuel...<br>";
        require_once $test_file;
        echo "✅ Fichier chargé<br>";

        $class_name = 'WP_PDF_Builder_Pro\\Data\\DataProviderInterface';
        $exists = class_exists($class_name);
        echo "Classe $class_name: " . ($exists ? "✅ EXISTE" : "❌ ABSENTE") . "<br>";

    } catch (Throwable $e) {
        echo "❌ Erreur chargement: " . $e->getMessage() . "<br>";
    }
}
echo "<hr>";

echo "<p><em>Debug étape par étape terminé à " . date('H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">d:\wp-pdf-builder-pro\plugin\debug-step-by-step.php