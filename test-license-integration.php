<?php
/**
 * Test script to verify license settings integration
 */

// Simulate form submission data
$test_input = [
    'pdf_builder_settings' => [
        'pdf_builder_license_email_reminders' => '1',
        'pdf_builder_license_reminder_email' => 'test@example.com',
        'pdf_builder_cache_enabled' => '1', // Another setting to verify it still works
    ]
];

// Include the SettingsManager
require_once __DIR__ . '/plugin/src/Admin/Managers/SettingsManager.php';

// Test the sanitizeSettings function
$settings_manager = new PDF_Builder_SettingsManager();
$sanitized = $settings_manager->sanitizeSettings($test_input);

echo "Test Results:\n";
echo "Input: " . json_encode($test_input, JSON_PRETTY_PRINT) . "\n";
echo "Sanitized: " . json_encode($sanitized, JSON_PRETTY_PRINT) . "\n";

// Check if license fields are properly sanitized
$license_reminders = $sanitized['pdf_builder_license_email_reminders'] ?? 'NOT SET';
$license_email = $sanitized['pdf_builder_license_reminder_email'] ?? 'NOT SET';
$cache_enabled = $sanitized['pdf_builder_cache_enabled'] ?? 'NOT SET';

echo "\nField Checks:\n";
echo "License Email Reminders: $license_reminders (expected: 1)\n";
echo "License Reminder Email: $license_email (expected: test@example.com)\n";
echo "Cache Enabled: $cache_enabled (expected: 1)\n";

if ($license_reminders === '1' && $license_email === 'test@example.com' && $cache_enabled === '1') {
    echo "\n✅ All tests passed! License settings are properly integrated.\n";
} else {
    echo "\n❌ Some tests failed. Check the sanitization logic.\n";
}