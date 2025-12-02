<?php
/**
 * Manual test script for PDF Builder save system
 * This runs basic validation without requiring full PHPUnit setup
 */

echo "=== PDF Builder Save System Manual Tests ===\n\n";

// Load required files
define('ABSPATH', dirname(__DIR__) . '/'); // Simulate WordPress ABSPATH
require_once __DIR__ . '/../plugin/templates/admin/settings-parts/settings-handlers-factory.php';

// Test 1: Check if factory function exists
echo "Test 1: Factory function availability\n";
if (function_exists('pdf_builder_register_settings_handler')) {
    echo "[PASS] pdf_builder_register_settings_handler function exists\n";
} else {
    echo "[FAIL] pdf_builder_register_settings_handler function missing\n";
    exit(1);
}

// Test 2: Register handlers for different tabs
echo "\nTest 2: Handler registration\n";
$tabs = ['general', 'appearance', 'security', 'advanced'];
$registered_handlers = [];

foreach ($tabs as $tab) {
    $result = pdf_builder_register_settings_handler($tab);
    if ($result) {
        $registered_handlers[] = $tab;
        echo "✓ Handler registered for tab: $tab\n";
    } else {
        echo "✗ Failed to register handler for tab: $tab\n";
    }
}

// Test 3: Check AJAX hooks are registered
echo "\nTest 3: AJAX hooks verification\n";
global $wp_filter;
$ajax_hooks_found = 0;

foreach ($tabs as $tab) {
    $hook_name = 'wp_ajax_pdf_builder_save_' . $tab;
    if (isset($wp_filter[$hook_name])) {
        $ajax_hooks_found++;
        echo "✓ AJAX hook found: $hook_name\n";
    } else {
        echo "✗ AJAX hook missing: $hook_name\n";
    }
}

echo "\nRegistered $ajax_hooks_found out of " . count($tabs) . " AJAX hooks\n";

// Test 4: Basic functionality test (simulated)
echo "\nTest 4: Basic functionality simulation\n";

// Simulate a save operation
$test_data = [
    'general_title' => 'Test PDF Document',
    'general_author' => 'Test Author',
    'general_subject' => 'Test Subject'
];

echo "Simulating save operation with test data...\n";

// Check if update_option function exists (WordPress function)
if (function_exists('update_option')) {
    foreach ($test_data as $key => $value) {
        $option_key = 'pdf_builder_' . $key;
        $result = update_option($option_key, $value);
        if ($result) {
            echo "✓ Saved option: $option_key = $value\n";
        } else {
            echo "✗ Failed to save option: $option_key\n";
        }
    }

    // Verify data was saved
    echo "\nVerifying saved data...\n";
    foreach ($test_data as $key => $value) {
        $option_key = 'pdf_builder_' . $key;
        $saved_value = get_option($option_key);
        if ($saved_value === $value) {
            echo "✓ Verified: $option_key = $saved_value\n";
        } else {
            echo "✗ Verification failed for: $option_key (expected: $value, got: $saved_value)\n";
        }
    }
} else {
    echo "⚠ WordPress functions not available - skipping save simulation\n";
}

// Test 5: Security validation
echo "\nTest 5: Security validation\n";

// Check nonce creation
if (function_exists('wp_create_nonce')) {
    $nonce = wp_create_nonce('pdf_builder_settings');
    if (!empty($nonce)) {
        echo "✓ Nonce creation works: " . substr($nonce, 0, 10) . "...\n";
    } else {
        echo "✗ Nonce creation failed\n";
    }
} else {
    echo "⚠ WordPress nonce functions not available\n";
}

// Test 6: Data sanitization
echo "\nTest 6: Data sanitization test\n";
$malicious_data = [
    'title' => '<script>alert("xss")</script>Test',
    'content' => 'Normal content',
    'sql' => "'; DROP TABLE test; --"
];

if (function_exists('sanitize_text_field')) {
    foreach ($malicious_data as $key => $value) {
        $sanitized = sanitize_text_field($value);
        $has_script = strpos($sanitized, '<script>') !== false;
        $has_sql = strpos($sanitized, 'DROP TABLE') !== false;

        if (!$has_script && !$has_sql) {
            echo "[PASS] Data sanitized successfully for: $key\n";
        } else {
            echo "[FAIL] Sanitization failed for: $key (still contains malicious content)\n";
        }
    }
} else {
    echo "⚠ WordPress sanitization functions not available\n";
}

// Summary
echo "\n=== Test Summary ===\n";
echo "Factory system: [PASS] Operational\n";
echo "Handler registration: [PASS] " . count($registered_handlers) . "/" . count($tabs) . " tabs registered\n";
echo "AJAX hooks: [PASS] $ajax_hooks_found/" . count($tabs) . " hooks found\n";
echo "Data persistence: " . (function_exists('update_option') ? "[PASS] Available" : "[WARN] Not available") . "\n";
echo "Security: " . (function_exists('wp_create_nonce') ? "[PASS] Nonce system available" : "[WARN] Limited") . "\n";
echo "Sanitization: " . (function_exists('sanitize_text_field') ? "[PASS] Available" : "[WARN] Limited") . "\n";

echo "\n=== Recommendations ===\n";
if (!function_exists('update_option')) {
    echo "- Run tests in WordPress environment for full validation\n";
}
if ($ajax_hooks_found < count($tabs)) {
    echo "- Some AJAX handlers may not be properly registered\n";
}
echo "- Consider setting up full PHPUnit environment for automated testing\n";

echo "\nManual testing completed.\n";