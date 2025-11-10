<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Preview_Generator.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Preview_Generator.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Preview_Generator', 'class PdfBuilderPreviewGenerator', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'generate_preview' => 'generatePreview',
    'get_cache_key' => 'getCacheKey',
    'generate_cache_key' => 'generateCacheKey',
    'init_dompdf' => 'initDompdf',
    'get_cached_preview' => 'getCachedPreview',
    'get_preview_data' => 'getPreviewData',
    'get_order_data' => 'getOrderData',
    'get_sample_data' => 'getSampleData',
    'get_order_items' => 'getOrderItems',
    'get_company_info' => 'getCompanyInfo',
    'render_pdf' => 'renderPdf',
    'generate_preview_html' => 'generatePreviewHtml',
    'render_template_element_html' => 'renderTemplateElementHtml',
    'process_text_content' => 'processTextContent',
    'save_and_get_url' => 'saveAndGetUrl',
    'get_cache_directory' => 'getCacheDirectory'
];

// 4. Corriger les appels de méthodes dans le fichier lui-même
foreach ($methodMappings as $old => $new) {
    // Remplacer les appels de méthodes (avec -> ou ::)
    $content = preg_replace('/(->|::)' . preg_quote($old, '/') . '\(/', '$1' . $new . '(', $content);
}

// 5. Corriger les déclarations de méthodes
foreach ($methodMappings as $old => $new) {
    $content = preg_replace('/function ' . preg_quote($old, '/') . '\(/', 'function ' . $new . '(', $content);
}

file_put_contents($filePath, $content);

echo "Corrections appliquées dans PDF_Builder_Preview_Generator.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Preview_Generator → PdfBuilderPreviewGenerator\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>