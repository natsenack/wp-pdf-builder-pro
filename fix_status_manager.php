<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Status_Manager.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Status_Manager.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Status_Manager', 'class PdfBuilderStatusManager', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'init_hooks' => 'initHooks',
    'detect_woocommerce_statuses' => 'detectWoocommerceStatuses',
    'detect_plugin_statuses' => 'detectPluginStatuses',
    'status_exists' => 'statusExists',
    'extend_status_settings' => 'extendStatusSettings',
    'get_default_template_id' => 'getDefaultTemplateId',
    'get_status_mappings' => 'getStatusMappings',
    'get_template_with_fallback' => 'getTemplateWithFallback',
    'log_status_detection' => 'logStatusDetection',
    'log_unknown_status_usage' => 'logUnknownStatusUsage',
    'daily_status_check' => 'dailyStatusCheck',
    'check_for_new_statuses' => 'checkForNewStatuses',
    'check_for_removed_statuses' => 'checkForRemovedStatuses',
    'get_status_info_for_admin' => 'getStatusInfoForAdmin'
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

echo "Corrections appliquées dans PDF_Builder_Status_Manager.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Status_Manager → PdfBuilderStatusManager\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>