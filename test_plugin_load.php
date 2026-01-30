<?php
/**
 * Test script for PDF Builder Pro plugin loading
 */

// Define WordPress path
define('ABSPATH', dirname(__FILE__) . '/../../wordpress/');

// Check if WordPress exists
if (!file_exists(ABSPATH . 'wp-load.php')) {
    echo "ERROR: WordPress not found at " . ABSPATH . "\n";
    exit(1);
}

// Load WordPress
require_once(ABSPATH . 'wp-load.php');

echo "WordPress loaded successfully\n";

// Define constants for the plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/plugin/');

echo "Plugin directory: " . PDF_BUILDER_PLUGIN_DIR . "\n";
echo "PDF Builder plugin file exists: " . (file_exists(PDF_BUILDER_PLUGIN_DIR . 'pdf-builder-pro.php') ? 'YES' : 'NO') . "\n";

// Try to load the main plugin file
try {
    echo "\nLoading plugin main file...\n";
    require_once(PDF_BUILDER_PLUGIN_DIR . 'pdf-builder-pro.php');
    echo "Plugin loaded successfully!\n";
} catch (Exception $e) {
    echo "ERROR loading plugin: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} catch (Throwable $t) {
    echo "FATAL ERROR loading plugin: " . $t->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $t->getTraceAsString() . "\n";
    exit(1);
}

echo "\nPlugin test completed successfully!\n";
?>
