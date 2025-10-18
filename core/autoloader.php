<?php
/**
 * PDF Builder Pro Autoloader
 *
 * PSR-4 compliant autoloader for the PDF Builder Pro plugin
 */

if (!defined('ABSPATH')) {
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
    ];

    /**
     * Initialize the autoloader
     */
    public static function init($base_path) {
        self::$base_path = trailingslashit($base_path);

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

            // Replace namespace separators with directory separators
            $file = self::$base_path . $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // If the file exists, require it
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
}

// Initialize the autoloader
PDF_Builder_Autoloader::init(dirname(__DIR__));