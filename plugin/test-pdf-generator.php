<?php
define('PHPUNIT_RUNNING', true);
require_once 'core/autoloader.php';
\PDF_Builder\Core\PdfBuilderAutoloader::init(__DIR__ . '/');

try {
    $data_provider = new \PDF_Builder\Data\SampleDataProvider();
    $template_data = ['id' => 'test'];
    $generator = new \PDF_Builder\Generators\PDFGenerator($template_data, $data_provider, true, []);
    echo '✅ PDFGenerator instantiated successfully' . PHP_EOL;
} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
}
?>