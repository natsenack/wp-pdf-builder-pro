<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Database_Query_Optimizer.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Database_Query_Optimizer.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Database_Query_Optimizer', 'class PdfBuilderDatabaseQueryOptimizer', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'initialize_optimizer' => 'initializeOptimizer',
    'prepare_common_queries' => 'prepareCommonQueries',
    'initialize_query_cache' => 'initializeQueryCache',
    'get_optimized_order_data' => 'getOptimizedOrderData',
    'get_optimized_product_data' => 'getOptimizedProductData',
    'optimize_woocommerce_query' => 'optimizeWoocommerceQuery',
    'add_index_hints' => 'addIndexHints',
    'optimize_joins' => 'optimizeJoins',
    'optimize_where_conditions' => 'optimizeWhereConditions',
    'measure_query_performance' => 'measureQueryPerformance',
    'create_performance_indexes' => 'createPerformanceIndexes',
    'analyze_slow_queries' => 'analyzeSlowQueries',
    'get_query_recommendations' => 'getQueryRecommendations',
    'get_optimization_stats' => 'getOptimizationStats',
    'get_cache_info' => 'getCacheInfo',
    'clear_query_cache' => 'clearQueryCache',
    'optimize_database' => 'optimizeDatabase'
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

echo "Corrections appliquées dans PDF_Builder_Database_Query_Optimizer.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Database_Query_Optimizer → PdfBuilderDatabaseQueryOptimizer\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>