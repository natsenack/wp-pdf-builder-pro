<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Extended_Cache_Manager.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Extended_Cache_Manager.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Extended_Cache_Manager', 'class PdfBuilderExtendedCacheManager', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'initialize_cache' => 'initializeCache',
    'ensure_db_table' => 'ensureDbTable',
    'set_memory_cache' => 'setMemoryCache',
    'get_memory_cache' => 'getMemoryCache',
    'delete_memory_cache' => 'deleteMemoryCache',
    'clear_all_memory_cache' => 'clearAllMemoryCache',
    'clear_memory_cache_by_type' => 'clearMemoryCacheByType',
    'cleanup_memory_cache' => 'cleanupMemoryCache',
    'set_file_cache' => 'setFileCache',
    'get_file_cache' => 'getFileCache',
    'delete_file_cache' => 'deleteFileCache',
    'clear_all_file_cache' => 'clearAllFileCache',
    'clear_file_cache_by_type' => 'clearFileCacheByType',
    'set_db_cache' => 'setDbCache',
    'get_db_cache' => 'getDbCache',
    'delete_db_cache' => 'deleteDbCache',
    'clear_all_db_cache' => 'clearAllDbCache',
    'clear_db_cache_by_type' => 'clearDbCacheByType',
    'get_cache_file_path' => 'getCacheFilePath',
    'should_use_file_cache' => 'shouldUseFileCache',
    'get_default_ttl' => 'getDefaultTtl',
    'delete_directory_contents' => 'deleteDirectoryContents',
    'schedule_cleanup' => 'scheduleCleanup',
    'cleanup_expired_cache' => 'cleanupExpiredCache',
    'cleanup_expired_files' => 'cleanupExpiredFiles',
    'get_cache_stats' => 'getCacheStats',
    'get_directory_size' => 'getDirectorySize',
    'optimize_performance' => 'optimizePerformance'
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

echo "Corrections appliquées dans PDF_Builder_Extended_Cache_Manager.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Extended_Cache_Manager → PdfBuilderExtendedCacheManager\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>