<?php
// Clear OPcache and WordPress cache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared\n";
}

if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "WordPress cache cleared\n";
}

// Clear all transients
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_%'");
echo "Transients cleared\n";

echo "Cache cleanup complete!";
?>
