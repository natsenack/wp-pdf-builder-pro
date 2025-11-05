<?php
/**
 * Debug script to check plugin URLs
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'core/constants.php';

echo "<h1>Plugin URL Debug</h1>";
echo "<p>PDF_BUILDER_PLUGIN_URL: " . PDF_BUILDER_PLUGIN_URL . "</p>";
echo "<p>plugin_dir_url(__FILE__): " . plugin_dir_url(__FILE__) . "</p>";
echo "<p>site_url(): " . site_url() . "</p>";
echo "<p>home_url(): " . home_url() . "</p>";

// Test specific template URLs
$templates = ['modern', 'classic', 'corporate', 'minimal'];
echo "<h2>Template Preview URLs:</h2>";
foreach ($templates as $template) {
    $url = PDF_BUILDER_PLUGIN_URL . 'assets/images/templates/' . $template . '-preview.svg?v=' . time();
    echo "<p>$template: <a href='$url' target='_blank'>$url</a></p>";
}