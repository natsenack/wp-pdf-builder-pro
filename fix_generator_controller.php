<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Generator_Controller.php
 */

$filePath = __DIR__ . '/plugin/src/Controllers/PDF_Generator_Controller.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Pro_Generator', 'class PdfBuilderProGenerator', $content);

// 2. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'generate_html_from_elements' => 'generateHtmlFromElements',
    'render_element_to_html' => 'renderElementToHtml',
    'render_element_content' => 'renderElementContent',
    'extract_element_styles' => 'extractElementStyles',
    'convert_property_to_css' => 'convertPropertyToCss',
    'generate_from_elements' => 'generateFromElements',
    'validate_elements' => 'validateElements',
    'generate_fallback_html' => 'generateFallbackHtml',
    'log_error' => 'logError',
    'replace_order_variables' => 'replaceOrderVariables',
    'format_complete_company_info' => 'formatCompleteCompanyInfo',
    'get_company_data' => 'getCompanyData',
    'format_company_info_by_template' => 'formatCompanyInfoByTemplate',
    'has_address_data' => 'hasAddressData',
    'format_address' => 'formatAddress',
    'get_table_styles' => 'getTableStyles',
    'generate_table_html_from_canvas_template' => 'generateTableHtmlFromCanvasTemplate',
    'create_fake_order_data_for_template' => 'createFakeOrderDataForTemplate',
    'create_fake_item_data' => 'createFakeItemData',
    'create_fake_fee_data' => 'createFakeFeeData',
    'create_real_order_data' => 'createRealOrderData',
    'extract_complete_item_data' => 'extractCompleteItemData',
    'extract_fee_item_data' => 'extractFeeItemData',
    'generate_fake_table_html' => 'generateFakeTableHtml',
    'replace_fake_data_with_real_data' => 'replaceFakeDataWithRealData'
];

// 3. Corriger les appels de méthodes dans le fichier lui-même
foreach ($methodMappings as $old => $new) {
    // Remplacer les appels de méthodes (avec -> ou ::)
    $content = preg_replace('/(->|::)' . preg_quote($old, '/') . '\(/', '$1' . $new . '(', $content);
}

// 4. Corriger les déclarations de méthodes
foreach ($methodMappings as $old => $new) {
    $content = preg_replace('/function ' . preg_quote($old, '/') . '\(/', 'function ' . $new . '(', $content);
}

file_put_contents($filePath, $content);

echo "Corrections appliquées dans PDF_Generator_Controller.php\n";
echo "- Nom de classe corrigé: PDF_Builder_Pro_Generator → PdfBuilderProGenerator\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>