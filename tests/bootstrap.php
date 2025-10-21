<?php
/**
 * Bootstrap for PHPUnit tests
 */

// Define test environment
define('PHPUNIT_RUNNING', true);

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load WordPress test functions if available
if (file_exists(__DIR__ . '/../wordpress-stubs/wordpress-stubs.php')) {
    require_once __DIR__ . '/../wordpress-stubs/wordpress-stubs.php';
}

// Load core bootstrap
require_once __DIR__ . '/../core/bootstrap.php';