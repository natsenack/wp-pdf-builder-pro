<?php
/**
 * Simple autoloader for DomPDF
 */

// Prevent multiple registrations
if (!function_exists('wp_pdf_builder_dompdf_autoload')) {
    function wp_pdf_builder_dompdf_autoload($class) {
        // DomPDF classes
        if (strpos($class, 'Dompdf\\') === 0) {
            $classPath = str_replace('Dompdf\\', '', $class);
            $filePath = __DIR__ . '/dompdf/src/' . str_replace('\\', '/', $classPath) . '.php';

            if (file_exists($filePath)) {
                require_once $filePath;
                return true;
            }
        }

        // Also try Cpdf class specifically
        if ($class === 'Dompdf\Cpdf' || $class === 'Cpdf') {
            $filePath = __DIR__ . '/dompdf/lib/Cpdf.php';
            if (file_exists($filePath)) {
                require_once $filePath;
                // Create alias if needed
                if (!class_exists('Dompdf\Cpdf', false) && class_exists('Cpdf', false)) {
                    class_alias('Cpdf', 'Dompdf\Cpdf');
                }
                return true;
            }
        }

        return false;
    }

    // Register the autoloader with high priority
    spl_autoload_register('wp_pdf_builder_dompdf_autoload', true, true);
}

// Pre-load critical DomPDF classes
$criticalClasses = [
    __DIR__ . '/dompdf/src/Dompdf.php',
    __DIR__ . '/dompdf/src/Options.php',
    __DIR__ . '/dompdf/lib/Cpdf.php',
    __DIR__ . '/dompdf/src/Canvas.php',
    __DIR__ . '/dompdf/src/FontMetrics.php'
];

foreach ($criticalClasses as $classFile) {
    if (file_exists($classFile)) {
        require_once $classFile;
    }
}

// Create class aliases if needed
if (class_exists('Cpdf') && !class_exists('Dompdf\Cpdf')) {
    class_alias('Cpdf', 'Dompdf\Cpdf');
}