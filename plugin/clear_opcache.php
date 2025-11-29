<?php
/**
 * Clear OPcache for PDF Builder Pro
 * Run this file once to clear cached PHP files
 */

// Load WordPress
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    die('WordPress not found');
}

// Only allow access if user is logged in and has admin capabilities
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied');
}

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo '<h2>OPcache cleared successfully!</h2>';
    echo '<p>The cached PHP files have been cleared. You can now delete this file.</p>';
} else {
    echo '<h2>OPcache not available</h2>';
    echo '<p>OPcache is not enabled on this server.</p>';
}

echo '<p><a href="' . admin_url('admin.php?page=pdf-builder-settings') . '">Return to PDF Builder Settings</a></p>';
?>