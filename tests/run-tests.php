<?php
/**
 * Lanceur de tests unitaires
 */

// Define test constants
define('PHPUNIT_RUNNING', true);
define('ABSPATH', dirname(__DIR__) . '/');
define('WPINC', 'wp-includes');

// Load only the necessary files directly
require_once __DIR__ . '/../stubs.php';

// Mock WordPress functions
require_once __DIR__ . '/bootstrap.php';

// Load the VariableMapper class directly
require_once __DIR__ . '/../src/Managers/PDF_Builder_Variable_Mapper.php';

// Run the tests
require_once __DIR__ . '/unit/PDF_Builder_Variable_Mapper_Standalone_Test.php';

$test = new PDF_Builder_Variable_Mapper_Standalone_Test();
$success = $test->run();

exit($success ? 0 : 1);