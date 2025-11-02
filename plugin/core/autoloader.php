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
                // Convert namespace parts to lowercase directories
                $parts = explode('\\', $relative_class);
                $relative_class = implode('/', array_map('lcfirst', $parts));
            } else {
                // Replace namespace separators with directory separators
                $relative_class = str_replace('\\', '/', $relative_class);
            }

            $file = self::$base_path . $base_dir . $relative_class . '.php';

            // Debug: uncomment for troubleshooting
            error_log("PDF_Builder_Autoloader: Looking for class '$class' in file '$file'");

            // Debug: uncomment for troubleshooting
            // error_log("PDF_Builder_Autoloader: Looking for class '$class' in file '$file'");

            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;

                // Verify the class was loaded
                if (class_exists($class, false)) {
                    return true;
                } else {
                    // Class not found in file - this might indicate a namespace mismatch
                    error_log("PDF_Builder_Autoloader: Class '$class' not found in expected file '$file'");
                }
            } else {
                // File not found - log for debugging
                error_log("PDF_Builder_Autoloader: File '$file' not found for class '$class'");
            }
        }

        return false;
    }
}

// Initialize the autoloader
PDF_Builder_Autoloader::init(dirname(__DIR__));