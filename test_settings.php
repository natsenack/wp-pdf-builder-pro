<?php
include 'plugin/pdf-builder-pro.php';
$settings = get_option('pdf_builder_settings', []);
echo 'Cache enabled: ' . ($settings['pdf_builder_cache_enabled'] ?? 'NOT SET') . PHP_EOL;
echo 'Cache compression: ' . ($settings['pdf_builder_cache_compression'] ?? 'NOT SET') . PHP_EOL;
echo 'Cache auto cleanup: ' . ($settings['pdf_builder_cache_auto_cleanup'] ?? 'NOT SET') . PHP_EOL;

// Also check individual options
echo PHP_EOL . 'Individual options:' . PHP_EOL;
echo 'Cache enabled (individual): ' . get_option('pdf_builder_cache_enabled', 'NOT SET') . PHP_EOL;
echo 'Cache compression (individual): ' . get_option('pdf_builder_cache_compression', 'NOT SET') . PHP_EOL;
echo 'Cache auto cleanup (individual): ' . get_option('pdf_builder_cache_auto_cleanup', 'NOT SET') . PHP_EOL;
?>