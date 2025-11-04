<?php
/**
 * Simple autoloader for DomPDF
 */

// Autoload DomPDF classes
spl_autoload_register(function ($class) {
    // DomPDF classes
    if (strpos($class, 'Dompdf\\') === 0) {
        $classPath = str_replace('Dompdf\\', '', $class);
        $filePath = __DIR__ . '/dompdf/src/' . str_replace('\\', '/', $classPath) . '.php';

        if (file_exists($filePath)) {
            require_once $filePath;
            return true;
        }
    }

    return false;
});