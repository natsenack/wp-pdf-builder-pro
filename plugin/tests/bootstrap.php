<?php
/**
 * Bootstrap for PHPUnit tests
 */

// Define test environment
define('PHPUNIT_RUNNING', true);

// Load WordPress test environment if available
$wp_tests_dir = getenv('WP_TESTS_DIR');
if (!$wp_tests_dir) {
    $wp_tests_dir = '/tmp/wordpress-tests-lib';
}

if (file_exists($wp_tests_dir . '/includes/functions.php')) {
    require_once $wp_tests_dir . '/includes/functions.php';
    require_once $wp_tests_dir . '/includes/bootstrap.php';
} else {
    // Fallback: load minimal WordPress environment
    $wp_load_path = dirname(dirname(__DIR__)) . '/../../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    }
}

// Load Composer autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Load plugin constants and core files
require_once dirname(__DIR__) . '/core/constants.php';
require_once dirname(__DIR__) . '/core/autoloader.php';

// Initialize autoloader
if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
    PDF_Builder\Core\PdfBuilderAutoloader::init(dirname(__DIR__));
}