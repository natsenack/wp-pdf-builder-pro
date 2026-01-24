<?php

/**
 * PDF Builder Pro Autoloader
 *
 * PSR-4 compliant autoloader for the PDF Builder Pro plugin
 */

namespace PDF_Builder\Core;

if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit;
}

class PdfBuilderAutoloader
{
    /**
     * Plugin base path
     */
    private static $base_path;
    /**
     * Namespace to path mappings
     */
    private static $prefixes = [
        'PDF_Builder\\' => 'src/',
        'PDF_Builder\Api\\' => 'api/',
        'PDF_Builder\Data\\' => 'config/data/',
        'PDF_Builder\Generators\\' => 'src/Generators/',
        'PDF_Builder\Helpers\\' => 'src/Helpers/',
        'PDF_Builder\Interfaces\\' => 'src/Interfaces/',
        'PDF_Builder\Templates\\' => 'resources/templates/',
        'PDF_Builder\Core\\' => 'src/Core/core/',
    ];    /**
           * Initialize the autoloader
           */
    public static function init($base_path)
    {
        // Add trailing slash if not present (equivalent to trailingslashit)
        self::$base_path = rtrim($base_path, '/') . '/';
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload function
     */
    public static function autoload($class)
    {
        // Skip Dompdf classes to avoid conflicts
        if (strpos($class, 'Dompdf') !== false) {
            return false;
        }

        // Special check for the problematic class
        if ($class === 'PDF_Builder\\Managers\\Dompdf\\Dompdf') {
            return false;
        }

        // Fallback: if any class contains 'Dompdf' and is in PDF_Builder namespace, skip it
        if (strpos($class, 'PDF_Builder') === 0 && strpos($class, 'Dompdf') !== false) {
            return false;
        }

        // Check if the class uses our namespace prefix
        foreach (self::$prefixes as $prefix => $base_dir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            // Get the relative class name
            $relative_class = substr($class, $len);

            // Replace namespace separators with directory separators
            $relative_class = str_replace('\\', '/', $relative_class);

            $file = self::$base_path . $base_dir . $relative_class . '.php';

            // If the file exists, require it
            if (file_exists($file)) {
                include_once $file;

                // Verify the class/interface was loaded
                if (class_exists($class, false) || interface_exists($class, false) 
                    || class_exists($class) || interface_exists($class)
                ) {
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
