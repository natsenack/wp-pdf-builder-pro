<?php
/**
 * PDF Builder Pro Autoloader
 *
 * PSR-4 compliant autoloader for the PDF Builder Pro plugin
 */

if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit;
}

class PDF_Builder_Autoloader {

    /**
     * Plugin base path
     */
    private static $base_path;

    /**
     * Namespace to path mappings
     */
    private static $prefixes = [
        'PDF_Builder\\' => 'src/',
        'WP_PDF_Builder_Pro\\' => '',
    ];

    /**
     * Initialize the autoloader
     */
    public static function init($base_path) {
        // Add trailing slash if not present (equivalent to trailingslashit)
        self::$base_path = rtrim($base_path, '/') . '/';

        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload function
     */
    public static function autoload($class) {
        // Check if the class uses our namespace prefix
        foreach (self::$prefixes as $prefix => $base_dir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            // Get the relative class name
            $relative_class = substr($class, $len);

            // Special handling for WP_PDF_Builder_Pro namespace
            if ($prefix === 'WP_PDF_Builder_Pro\\') {
                // Convert namespace parts to lowercase directories but keep class names as-is
                $parts = explode('\\', $relative_class);
                $lastPart = array_pop($parts); // Extract the class name
                $dirs = array_map('lcfirst', $parts); // Convert directory names to lowercase
                $relative_class = implode('/', $dirs) . '/' . $lastPart; // Rebuild path
            } else {
                // Replace namespace separators with directory separators
                $relative_class = str_replace('\\', '/', $relative_class);
            }

            $file = self::$base_path . $base_dir . $relative_class . '.php';

            // Debug: uncomment for troubleshooting


            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;

                // Verify the class/interface was loaded
                if (class_exists($class, false) || interface_exists($class, false) || 
                    class_exists($class) || interface_exists($class)) {
                    return true;
                } else {
                    // Class/interface not found in file - this might indicate a namespace mismatch

                }
            } else {
                // File not found - log for debugging

            }
        }

        return false;
    }
}

// Initialize the autoloader
PDF_Builder_Autoloader::init(dirname(__DIR__));
