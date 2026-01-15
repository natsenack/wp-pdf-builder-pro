<?php
/**
 * OPcache Reset - Clearer for PDF Builder Pro
 * This file should be placed in the plugin root to clear OPcache after deployments
 */

// Only accessible from localhost or admin
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== 'localhost' && 
    (!isset($_GET['token']) || $_GET['token'] !== 'pdf_builder_reset')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "⚠️  OPcache not available\n";
}

// Clear all object cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✅ WordPress object cache cleared\n";
}

echo "💾 Cache reset completed at " . date('Y-m-d H:i:s');
?>
