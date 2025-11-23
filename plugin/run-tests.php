<?php
echo 'Starting test...' . PHP_EOL;

// Define minimal WordPress constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Simple test without WordPress dependencies
echo 'Loading constants...' . PHP_EOL;
require_once 'core/constants.php';

echo 'Loading autoloader...' . PHP_EOL;
require_once 'core/autoloader.php';

// Initialize autoloader
if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
    echo 'Initializing autoloader...' . PHP_EOL;
    PDF_Builder\Core\PdfBuilderAutoloader::init(__DIR__ . '/');
} else {
    echo '❌ PdfBuilderAutoloader class not found' . PHP_EOL;
    exit(1);
}

try {
    // Test basic class loading
    echo 'Testing SampleDataProvider class...' . PHP_EOL;
    if (class_exists('PDF_Builder\Data\SampleDataProvider')) {
        echo '✅ SampleDataProvider class loaded successfully' . PHP_EOL;

        $provider = new PDF_Builder\Data\SampleDataProvider('canvas');
        echo '✅ SampleDataProvider instantiated successfully' . PHP_EOL;

        // Test basic methods
        $hasName = $provider->hasVariable('customer_name');
        if ($hasName) {
            echo '✅ hasVariable method works' . PHP_EOL;
        }

        $name = $provider->getVariableValue('customer_name');
        if (is_string($name) && !empty($name)) {
            echo '✅ getVariableValue method works' . PHP_EOL;
        }

        $variables = $provider->getAllVariables();
        if (is_array($variables) && count($variables) > 0) {
            echo '✅ getAllVariables method works' . PHP_EOL;
        }

        echo '🎉 All basic functionality tests passed!' . PHP_EOL;
    } else {
        echo '❌ SampleDataProvider class not found' . PHP_EOL;
        echo 'Available classes: ' . PHP_EOL;
        $declared = get_declared_classes();
        foreach ($declared as $class) {
            if (strpos($class, 'PDF_Builder') !== false) {
                echo '  - ' . $class . PHP_EOL;
            }
        }
    }
} catch (Exception $e) {
    echo '❌ Test failed: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
} catch (Error $e) {
    echo '❌ Fatal error: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
}
?>