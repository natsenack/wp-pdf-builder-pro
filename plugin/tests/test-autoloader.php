<?php
define('PHPUNIT_RUNNING', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing autoloader paths...\n";

$base_path = __DIR__ . '/';
echo "Base path: $base_path\n";

$generator_file = $base_path . 'generators/GeneratorManager.php';
echo "GeneratorManager file: $generator_file\n";
echo "File exists: " . (file_exists($generator_file) ? 'YES' : 'NO') . "\n";

$api_file = $base_path . 'api/PreviewImageAPI.php';
echo "PreviewImageAPI file: $api_file\n";
echo "File exists: " . (file_exists($api_file) ? 'YES' : 'NO') . "\n";

echo "\nTesting autoloader...\n";

try {
    require_once 'core/autoloader.php';

    echo "Autoloader class exists: " . (class_exists('PDF_Builder\Core\PdfBuilderAutoloader') ? 'YES' : 'NO') . "\n";

    \PDF_Builder\Core\PdfBuilderAutoloader::init(__DIR__);

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
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}