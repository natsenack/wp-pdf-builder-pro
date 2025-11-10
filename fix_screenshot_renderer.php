<?php
/**
 * Fix PSR12 violations in PDF_Builder_Screenshot_Renderer.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Screenshot_Renderer.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Screenshot_Renderer', 'class PdfBuilderScreenshotRenderer', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'generate_pdf_from_canvas' => 'generatePdfFromCanvas',
    'generate_canvas_html' => 'generateCanvasHtml',
    'render_canvas_element' => 'renderCanvasElement',
    'build_element_style' => 'buildElementStyle',
    'generate_pdf_from_html' => 'generatePdfFromHtml',
    'is_wkhtmltopdf_available' => 'isWkhtmltopdfAvailable',
    'generate_with_wkhtmltopdf' => 'generateWithWkhtmltopdf',
    'is_puppeteer_available' => 'isPuppeteerAvailable',
    'generate_with_puppeteer' => 'generateWithPuppeteer',
    'generate_with_dompdf_fallback' => 'generateWithDompdfFallback',
    'get_system_capabilities' => 'getSystemCapabilities'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Screenshot_Renderer::$old\(/", "PdfBuilderScreenshotRenderer::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Screenshot_Renderer.php', $content);

echo "Fixed PDF_Builder_Screenshot_Renderer.php\n";