<?php
define('PHPUNIT_RUNNING', true);
define('ABSPATH', __DIR__ . '/');
require_once 'core/autoloader.php';
\PDF_Builder\Core\PdfBuilderAutoloader::init(__DIR__ . '/');

try {
    // Test direct du GeneratorManager avec SampleDataProvider
    $generator_manager = new \PDF_Builder\Generators\GeneratorManager();
    $data_provider = new \PDF_Builder\Data\SampleDataProvider();

    echo '✅ GeneratorManager and SampleDataProvider instantiated successfully' . PHP_EOL;

    // Données de template simples
    $template_data = [
        'template' => [
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'Test PDF Generation - Jour 3-4',
                    'style' => ['fontSize' => '16px', 'color' => '#000']
                ]
            ]
        ]
    ];

    // Configuration pour DomPDF
    $config = [
        'dpi' => 150,
        'paper_size' => 'A4',
        'orientation' => 'portrait'
    ];

    echo '🚀 Testing PDF generation with DomPDF...' . PHP_EOL;

    $result = $generator_manager->generatePreview(
        $template_data,
        $data_provider,
        'pdf',
        $config
    );

    if ($result !== false) {
        echo '✅ PDF generation successful!' . PHP_EOL;
        echo 'Generated content length: ' . strlen($result) . ' bytes' . PHP_EOL;
        echo 'Attempt history: ' . json_encode($generator_manager->getAttemptHistory()) . PHP_EOL;
    } else {
        echo '❌ PDF generation failed' . PHP_EOL;
        echo 'Attempt history: ' . json_encode($generator_manager->getAttemptHistory()) . PHP_EOL;
    }

} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
}
?>