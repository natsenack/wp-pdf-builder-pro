<?php
/**
 * Debug script for autoloader on server
 * This file can be accessed directly to test autoloader functionality
 */

define('PHPUNIT_RUNNING', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PDF Builder Pro Autoloader Debug</h1>";
echo "<pre>";

echo "Testing autoloader paths...\n";

$base_path = dirname(__FILE__) . '/';
echo "Base path: $base_path\n";

$generator_file = $base_path . 'generators/GeneratorManager.php';
echo "GeneratorManager file: $generator_file\n";
echo "File exists: " . (file_exists($generator_file) ? 'YES' : 'NO') . "\n";

$api_file = $base_path . 'api/PreviewImageAPI.php';
echo "PreviewImageAPI file: $api_file\n";
echo "File exists: " . (file_exists($api_file) ? 'YES' : 'NO') . "\n";

$autoloader_file = $base_path . 'core/autoloader.php';
echo "Autoloader file: $autoloader_file\n";
echo "File exists: " . (file_exists($autoloader_file) ? 'YES' : 'NO') . "\n";

echo "\nTesting autoloader...\n";

try {
    require_once $autoloader_file;

    echo "Autoloader class exists: " . (class_exists('PDF_Builder\Core\PdfBuilderAutoloader') ? 'YES' : 'NO') . "\n";

    \PDF_Builder\Core\PdfBuilderAutoloader::init($base_path);

    $functions = spl_autoload_functions();
    echo "Autoload functions registered: " . count($functions) . "\n";

    if (class_exists('PDF_Builder\Generators\GeneratorManager')) {
        echo "✅ GeneratorManager loaded successfully\n";
    } else {
        echo "❌ GeneratorManager not found\n";
    }

    if (class_exists('PDF_Builder\Api\PreviewImageAPI')) {
        echo "✅ PreviewImageAPI loaded successfully\n";
    } else {
        echo "❌ PreviewImageAPI not found\n";
    }

    echo "\nTesting PreviewImageAPI instantiation...\n";
    $api = new \PDF_Builder\Api\PreviewImageAPI();
    echo "✅ PreviewImageAPI instantiated successfully\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>