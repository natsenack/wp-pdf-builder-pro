<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Asset_Optimizer.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Asset_Optimizer.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace (en supposant qu'il devrait être dans le namespace du plugin)
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Asset_Optimizer', 'class PdfBuilderAssetOptimizer', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'initialize_optimizer' => 'initializeOptimizer',
    'optimize_all_assets' => 'optimizeAllAssets',
    'optimize_javascript_assets' => 'optimizeJavascriptAssets',
    'optimize_css_assets' => 'optimizeCssAssets',
    'optimize_image_assets' => 'optimizeImageAssets',
    'optimize_html_templates' => 'optimizeHtmlTemplates',
    'optimize_javascript_file' => 'optimizeJavascriptFile',
    'optimize_css_file' => 'optimizeCssFile',
    'optimize_image_file' => 'optimizeImageFile',
    'optimize_html_file' => 'optimizeHtmlFile',
    'combine_javascript_files' => 'combineJavascriptFiles',
    'combine_css_files' => 'combineCssFiles',
    'minify_javascript' => 'minifyJavascript',
    'minify_css' => 'minifyCss',
    'minify_html' => 'minifyHtml',
    'optimize_jpeg' => 'optimizeJpeg',
    'optimize_png' => 'optimizePng',
    'optimize_gif' => 'optimizeGif',
    'get_plugin_js_files' => 'getPluginJsFiles',
    'get_plugin_css_files' => 'getPluginCssFiles',
    'get_plugin_image_files' => 'getPluginImageFiles',
    'get_plugin_html_templates' => 'getPluginHtmlTemplates',
    'log_optimization_results' => 'logOptimizationResults',
    'get_optimized_asset_urls' => 'getOptimizedAssetUrls',
    'cleanup_optimized_assets' => 'cleanupOptimizedAssets',
    'cleanup_directory' => 'cleanupDirectory',
    'get_optimization_stats' => 'getOptimizationStats',
    'get_directory_size' => 'getDirectorySize'
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

echo "Corrections appliquées dans PDF_Builder_Asset_Optimizer.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Asset_Optimizer → PdfBuilderAssetOptimizer\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>