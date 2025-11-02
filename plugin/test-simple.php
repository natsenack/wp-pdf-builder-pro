<?php
/**
 * Test Script Simplifié - PDF Builder Pro
 * Test de base sans dépendances WordPress complexes
 */

// Test 1: PHP et serveur
echo "<h1>🧪 Test Simplifié PDF Builder Pro</h1>";
echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<hr>";

// Test 2: Fichiers du plugin
echo "<h2>📁 Test des fichiers du plugin</h2>";

$plugin_dir = dirname(__FILE__) . '/';
$test_files = [
    'pdf-builder-pro.php',
    'bootstrap.php',
    'composer.json',
    'core/autoloader.php',
    'data/DataProviderInterface.php',
    'data/SampleDataProvider.php',
    'generators/BaseGenerator.php',
    'generators/PDFGenerator.php',
    'api/PreviewImageAPI.php'
];

foreach ($test_files as $file) {
    $full_path = $plugin_dir . $file;
    $exists = file_exists($full_path);
    $size = $exists ? filesize($full_path) : 0;
    echo ($exists ? "✅" : "❌") . " $file (" . number_format($size) . " bytes)<br>";
}

echo "<hr>";

// Test 3: Classes PHP (chargement direct)
echo "<h2>📦 Test du chargement des classes</h2>";

$classes_to_test = [
    'WP_PDF_Builder_Pro\Data\DataProviderInterface',
    'WP_PDF_Builder_Pro\Data\SampleDataProvider',
    'WP_PDF_Builder_Pro\Generators\BaseGenerator',
    'WP_PDF_Builder_Pro\Generators\PDFGenerator'
];

foreach ($classes_to_test as $class) {
    $exists = class_exists($class, false);
    echo ($exists ? "✅" : "❌") . " $class<br>";
}

echo "<hr>";

// Test 4: Autoloader
echo "<h2>🔄 Test de l'autoloader</h2>";
try {
    if (file_exists($plugin_dir . 'core/autoloader.php')) {
        require_once $plugin_dir . 'core/autoloader.php';
        echo "✅ Autoloader chargé<br>";

        // Re-test des classes après autoloader
        foreach ($classes_to_test as $class) {
            $exists = class_exists($class, false);
            echo ($exists ? "✅" : "❌") . " $class (après autoloader)<br>";
        }
    } else {
        echo "❌ Autoloader introuvable<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur autoloader: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 5: WordPress (si disponible)
echo "<h2>📘 Test WordPress</h2>";
if (defined('ABSPATH')) {
    echo "✅ WordPress détecté (ABSPATH défini)<br>";
    echo "📍 ABSPATH: " . ABSPATH . "<br>";

    if (function_exists('get_bloginfo')) {
        echo "✅ Fonctions WordPress disponibles<br>";
        echo "📝 Version WordPress: " . get_bloginfo('version') . "<br>";
    } else {
        echo "⚠️ Fonctions WordPress non disponibles<br>";
    }

    if (function_exists('is_plugin_active')) {
        $plugin_active = is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php');
        echo ($plugin_active ? "✅" : "❌") . " Plugin activé<br>";
    } else {
        echo "⚠️ Fonction is_plugin_active non disponible<br>";
    }
} else {
    echo "❌ WordPress non détecté (ABSPATH non défini)<br>";
}

echo "<hr>";

// Test 6: WooCommerce
echo "<h2>🛒 Test WooCommerce</h2>";
if (class_exists('WooCommerce')) {
    echo "✅ WooCommerce détecté<br>";
    if (defined('WC_VERSION')) {
        echo "📝 Version WooCommerce: " . WC_VERSION . "<br>";
    } else {
        echo "📝 Version WooCommerce: inconnue<br>";
    }
} else {
    echo "❌ WooCommerce non détecté<br>";
}

echo "<hr>";

// Test 7: Permissions
echo "<h2>🔐 Test des permissions</h2>";
$test_dirs = [
    'assets',
    'assets/js',
    'assets/js/dist',
    'vendor',
    'lib'
];

foreach ($test_dirs as $dir) {
    $full_path = $plugin_dir . $dir;
    $readable = is_readable($full_path);
    $writable = is_writable($full_path);
    echo ($readable ? "✅" : "❌") . " $dir (lecture) | ";
    echo ($writable ? "✅" : "❌") . " $dir (écriture)<br>";
}

echo "<hr>";

// Test 8: Mémoire et performance
echo "<h2>⚡ Test performance</h2>";
$start_time = microtime(true);
$memory_start = memory_get_usage(true);

echo "⏱️ Temps d'exécution: " . round(microtime(true) - $start_time, 4) . "s<br>";
echo "💾 Mémoire utilisée: " . number_format(memory_get_usage(true) - $memory_start) . " bytes<br>";
echo "💾 Pic mémoire: " . number_format(memory_get_peak_usage(true)) . " bytes<br>";

echo "<hr>";

// Instructions
echo "<h2>🎯 Prochaines étapes</h2>";
echo "<ol>";
echo "<li><strong>Si erreurs rouges:</strong> Vérifier l'activation du plugin</li>";
echo "<li><strong>Si classes non chargées:</strong> Problème d'autoloader</li>";
echo "<li><strong>Si WordPress non détecté:</strong> Accès direct au fichier PHP</li>";
echo "<li><strong>Test complet:</strong> <a href='test-server-current.php'>Version complète</a></li>";
echo "</ol>";

echo "<p><em>Tests terminés à " . date('H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">d:\wp-pdf-builder-pro\plugin\test-simple.php