<?php
$content = file_get_contents('plugin/src/utilities/JSON_Optimizer.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Src\\Utilities;\n\n",
    $content
);

// Rename class to PascalCase
$content = str_replace('class PDF_Builder_JSON_Optimizer', 'class PdfBuilderJsonOptimizer', $content);

// Convert method names from snake_case to camelCase
$content = str_replace('minify_json', 'minifyJson', $content);
$content = str_replace('deduplicate_values', 'deduplicateValues', $content);
$content = str_replace('_deduplicate_recursive', 'deduplicateRecursive', $content);
$content = str_replace('get_compression_stats', 'getCompressionStats', $content);
$content = str_replace('optimize_template', 'optimizeTemplate', $content);
$content = str_replace('set_compression_threshold', 'setCompressionThreshold', $content);

file_put_contents('plugin/src/utilities/JSON_Optimizer.php', $content);
echo "Fixed JSON_Optimizer.php\n";