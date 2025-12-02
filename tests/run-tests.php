<?php
/**
 * Test runner script for PDF Builder
 *
 * Usage: php run-tests.php [test_class] [options]
 *
 * Examples:
 *   php run-tests.php                    # Run all tests
 *   php run-tests.php SettingsSaveTest  # Run specific test class
 *   php run-tests.php --coverage         # Run with coverage
 */

require_once __DIR__ . '/bootstrap.php';

// Parse command line arguments
$args = $argv;
array_shift($args); // Remove script name

$test_class = null;
$coverage = false;
$verbose = false;

foreach ($args as $arg) {
    if ($arg === '--coverage') {
        $coverage = true;
    } elseif ($arg === '--verbose' || $arg === '-v') {
        $verbose = true;
    } elseif (!$test_class && strpos($arg, '--') !== 0) {
        $test_class = $arg;
    }
}

// Build PHPUnit command
$phpunit_cmd = 'vendor/bin/phpunit';

if ($test_class) {
    $phpunit_cmd .= ' --filter ' . escapeshellarg($test_class);
}

if ($coverage) {
    $phpunit_cmd .= ' --coverage-html coverage-report';
}

if ($verbose) {
    $phpunit_cmd .= ' --verbose';
}

// Execute tests
echo "Running PDF Builder tests...\n";
echo "Command: $phpunit_cmd\n\n";

$exit_code = 0;
passthru($phpunit_cmd, $exit_code);

if ($exit_code === 0) {
    echo "\n[SUCCESS] All tests passed!\n";
} else {
    echo "\n[FAILED] Some tests failed. Exit code: $exit_code\n";
}

exit($exit_code);</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\tests\run-tests.php