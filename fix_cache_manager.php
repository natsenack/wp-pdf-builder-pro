<?php
/**
 * Fix PSR12 violations in PDF_Builder_Cache_Manager.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Cache_Manager.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Cache_Manager', 'class PdfBuilderCacheManager', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'generate_key' => 'generateKey',
    'cleanup_expired_cache' => 'cleanupExpiredCache',
    'get_stats' => 'getStats'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Cache_Manager::$old\(/", "PdfBuilderCacheManager::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Cache_Manager.php', $content);

echo "Fixed PDF_Builder_Cache_Manager.php\n";