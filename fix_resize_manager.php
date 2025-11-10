<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Resize_Manager.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Resize_Manager.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Resize_Manager', 'class PdfBuilderResizeManager', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'init_dependencies' => 'initDependencies',
    'start_resize_session' => 'startResizeSession',
    'update_resize_dimensions' => 'updateResizeDimensions',
    'apply_aspect_ratio' => 'applyAspectRatio',
    'end_resize_session' => 'endResizeSession',
    'get_resize_session' => 'getResizeSession',
    'cleanup_resize_session' => 'cleanupResizeSession',
    'validate_resize_data' => 'validateResizeData',
    'calculate_resize_constraints' => 'calculateResizeConstraints',
    'get_resize_cursor' => 'getResizeCursor',
    'log_resize_event' => 'logResizeEvent',
    'get_resize_performance_stats' => 'getResizePerformanceStats'
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

echo "Corrections appliquées dans PDF_Builder_Resize_Manager.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Resize_Manager → PdfBuilderResizeManager\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>