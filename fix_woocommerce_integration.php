<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_WooCommerce_Integration.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_WooCommerce_Integration.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace (vérifier d'abord s'il n'existe pas déjà)
if (strpos($content, 'namespace ') === false) {
    $content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);
}

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_WooCommerce_Integration', 'class PdfBuilderWooCommerceIntegration', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'init_hooks' => 'initHooks',
    'register_ajax_hooks' => 'registerAjaxHooks',
    'detect_document_type' => 'detectDocumentType',
    'get_document_type_label' => 'getDocumentTypeLabel',
    'add_woocommerce_order_meta_box' => 'addWoocommerceOrderMetaBox',
    'render_woocommerce_order_meta_box' => 'renderWoocommerceOrderMetaBox',
    'ajax_generate_order_pdf' => 'ajaxGenerateOrderPdf',
    'get_nonce' => 'getNonce',
    'get_ajax_url' => 'getAjaxUrl',
    'get_template_for_order' => 'getTemplateForOrder',
    'build_element_style' => 'buildElementStyle',
    'render_element_content' => 'renderElementContent',
    'replace_order_variables' => 'replaceOrderVariables',
    'ajax_save_order_canvas' => 'ajaxSaveOrderCanvas',
    'sanitize_canvas_elements' => 'sanitizeCanvasElements',
    'sanitize_element_content' => 'sanitizeElementContent',
    'sanitize_element_styles' => 'sanitizeElementStyles',
    'ajax_load_order_canvas' => 'ajaxLoadOrderCanvas',
    'ajax_get_canvas_elements' => 'ajaxGetCanvasElements',
    'validate_and_clean_canvas_elements' => 'validateAndCleanCanvasElements',
    'clean_canvas_element' => 'cleanCanvasElement',
    'sanitize_element_field' => 'sanitizeElementField',
    'get_and_validate_order' => 'getAndValidateOrder',
    'get_order_items_complete_data' => 'getOrderItemsCompleteData',
    'ajax_get_order_data' => 'ajaxGetOrderData',
    'ajax_validate_order_access' => 'ajaxValidateOrderAccess',
    'ajax_get_company_data' => 'ajaxGetCompanyData'
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

echo "Corrections appliquées dans PDF_Builder_WooCommerce_Integration.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_WooCommerce_Integration → PdfBuilderWooCommerceIntegration\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>