<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_PDF_Generator.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_PDF_Generator.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_PDF_Generator', 'class PdfBuilderPdfGenerator', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'init_hooks' => 'initHooks',
    'ajax_download_pdf' => 'ajaxDownloadPdf',
    'generate_pdf_from_template_data' => 'generatePdfFromTemplateData',
    'generate_html_from_template_data' => 'generateHtmlFromTemplateData',
    'generate_unified_html_legacy' => 'generateUnifiedHtmlLegacy',
    'convert_elements_to_template' => 'convertElementsToTemplate',
    'generate_html_from_elements' => 'generateHtmlFromElements',
    'render_element_to_html' => 'renderElementToHtml',
    'generate_pdf' => 'generatePdf',
    'save_pdf' => 'savePdf',
    'render_template' => 'renderTemplate',
    'apply_context_to_html' => 'applyContextToHtml',
    'replace_order_variables' => 'replaceOrderVariables'
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

echo "Corrections appliquées dans PDF_Builder_PDF_Generator.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_PDF_Generator → PdfBuilderPdfGenerator\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>