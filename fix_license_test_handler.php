<?php
/**
 * Fix PSR12 violations in license-test-handler.php
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/License/license-test-handler.php');

// Rename class
$content = str_replace('class License_Test_Handler', 'class LicenseTestHandler', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'get_instance' => 'getInstance',
    'generate_test_key' => 'generateTestKey',
    'save_test_key' => 'saveTestKey',
    'get_test_key' => 'getTestKey',
    'set_test_mode_enabled' => 'setTestModeEnabled',
    'is_test_mode_enabled' => 'isTestModeEnabled',
    'handle_generate_test_key' => 'handleGenerateTestKey',
    'handle_validate_test_key' => 'handleValidateTestKey',
    'handle_toggle_test_mode' => 'handleToggleTestMode',
    'handle_delete_test_key' => 'handleDeleteTestKey',
    'handle_cleanup_license' => 'handleCleanupLicense'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/License_Test_Handler::$old\(/", "LicenseTestHandler::$new(", $content);
}

file_put_contents('plugin/src/License/license-test-handler.php', $content);

echo "Fixed license-test-handler.php\n";