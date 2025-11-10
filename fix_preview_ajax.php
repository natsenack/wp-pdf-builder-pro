<?php
/**
 * Fix PSR12 violations in PDF_Builder_Preview_Ajax.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/AJAX/PDF_Builder_Preview_Ajax.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\AJAX;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Preview_Ajax', 'class PdfBuilderPreviewAjax', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'generate_preview' => 'generatePreview',
    'get_preview_data' => 'getPreviewData',
    'get_sample_orders' => 'getSampleOrders',
    'get_recent_orders' => 'getRecentOrders',
    'get_company_info' => 'getCompanyInfo'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Preview_Ajax::$old\(/", "PdfBuilderPreviewAjax::$new(", $content);
}

file_put_contents('plugin/src/AJAX/PDF_Builder_Preview_Ajax.php', $content);

echo "Fixed PDF_Builder_Preview_Ajax.php\n";