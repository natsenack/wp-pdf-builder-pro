<?php
/**
 * Bootstrap for PHPUnit tests
 * Loads WordPress test environment
 */

// Define test constants
define('WP_TESTS_DIR', getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib');
define('WP_TESTS_CONFIG_FILE_PATH', getenv('WP_TESTS_CONFIG_FILE_PATH') ?: '/tmp/wp-tests-config.php');

// Check if WordPress test library exists
if (!file_exists(WP_TESTS_DIR . '/includes/functions.php')) {
    echo "WordPress test library not found at: " . WP_TESTS_DIR . PHP_EOL;
    echo "Please install WordPress test library or set WP_TESTS_DIR environment variable." . PHP_EOL;
    exit(1);
}

// Load WordPress test functions
require_once WP_TESTS_DIR . '/includes/functions.php';

// Load the test environment
require_once WP_TESTS_DIR . '/includes/bootstrap.php';

// Load our plugin
require_once dirname(__DIR__) . '/plugin/pdf-builder-pro.php';

// Load test utilities
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/AjaxTestCase.php';