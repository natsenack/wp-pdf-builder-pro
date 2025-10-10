<?php
/**
 * Diagnostic script for PDF Builder Pro template JSON issues
 * This script examines template ID 131 and attempts to repair corrupted JSON data
 */

// Allow direct access for CLI execution
if (php_sapi_name() !== 'cli') {
    die('Direct access forbidden.');
}

// Define WordPress constants manually for CLI access
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

// Load WordPress environment
require_once ABSPATH . 'wp-load.php';

// Now we can use WordPress functions
global $wpdb;

// Get the template data from database
$table_name = $wpdb->prefix . 'pdf_builder_templates';

$template = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", 131)
);

if (!$template) {
    echo "Template ID 131 not found in database.\n";
    exit(1);
}

echo "=== Template ID 131 Diagnostic ===\n";
echo "Template Name: " . $template->name . "\n";
echo "Template Data Length: " . strlen($template->template_data) . " characters\n";
echo "Created: " . $template->created_at . "\n";
echo "Updated: " . $template->updated_at . "\n\n";

echo "=== Raw Template Data (first 500 chars) ===\n";
echo substr($template->template_data, 0, 500) . "\n\n";

// Test JSON decoding
echo "=== JSON Validation ===\n";
$json_test = json_decode($template->template_data, true);
if ($json_test === null) {
    $error_msg = json_last_error_msg();
    $error_code = json_last_error();
    echo "❌ JSON is INVALID\n";
    echo "Error: $error_msg (code: $error_code)\n\n";

    // Show problematic sections
    echo "=== Problematic JSON Sections ===\n";
    $lines = explode("\n", $template->template_data);
    $problem_lines = [];
    foreach ($lines as $i => $line) {
        if (strpos($line, '�') !== false || preg_match('/[\x00-\x1F\x7F]/', $line)) {
            $problem_lines[] = "Line " . ($i + 1) . ": " . trim($line);
        }
    }

    if (empty($problem_lines)) {
        echo "No obvious character encoding issues found.\n";
        echo "The JSON might have structural problems.\n\n";
    } else {
        echo implode("\n", array_slice($problem_lines, 0, 10)) . "\n";
        if (count($problem_lines) > 10) {
            echo "... and " . (count($problem_lines) - 10) . " more problematic lines\n";
        }
        echo "\n";
    }

    // Try basic cleaning
    echo "=== Attempting Basic JSON Cleaning ===\n";
    $cleaned = $template->template_data;

    // Remove control characters
    $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $cleaned);

    // Fix trailing commas
    $cleaned = preg_replace('/,(\s*[}\]])/', '$1', $cleaned);

    // Fix unquoted keys
    $cleaned = preg_replace('/([{\s,])(\w+):/', '$1"$2":', $cleaned);

    $test_clean = json_decode($cleaned, true);
    if ($test_clean !== null) {
        echo "✅ Basic cleaning SUCCESSFUL\n";
        // Update the database
        $result = $wpdb->update(
            $table_name,
            ['template_data' => $cleaned],
            ['id' => 131]
        );
        if ($result !== false) {
            echo "✅ Template ID 131 updated with cleaned JSON\n";
        } else {
            echo "❌ Failed to update template\n";
        }
    } else {
        echo "❌ Basic cleaning FAILED\n";
        echo "Manual repair required. Here's the full JSON:\n\n";
        echo $template->template_data . "\n";
    }

} else {
    echo "✅ JSON is VALID\n";
    echo "Template structure appears correct.\n";
}

echo "\n=== Diagnostic Complete ===\n";

// Initialize the admin class
$admin = new PDF_Builder_Admin();

// Get the template data from database
global $wpdb;
$table_name = $wpdb->prefix . 'pdf_builder_templates';

$template = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", 131)
);

if (!$template) {
    echo "Template ID 131 not found in database.\n";
    exit(1);
}

echo "=== Template ID 131 Diagnostic ===\n";
echo "Template Name: " . $template->name . "\n";
echo "Template Data Length: " . strlen($template->template_data) . " characters\n";
echo "Created: " . $template->created_at . "\n";
echo "Updated: " . $template->updated_at . "\n\n";

echo "=== Raw Template Data (first 500 chars) ===\n";
echo substr($template->template_data, 0, 500) . "\n\n";

// Test JSON decoding
echo "=== JSON Validation ===\n";
$json_test = json_decode($template->template_data, true);
if ($json_test === null) {
    $error_msg = json_last_error_msg();
    $error_code = json_last_error();
    echo "❌ JSON is INVALID\n";
    echo "Error: $error_msg (code: $error_code)\n\n";

    // Try the clean_json_data method
    echo "=== Attempting JSON Cleaning ===\n";
    $cleaned_json = $admin->clean_json_data($template->template_data);
    echo "Cleaned JSON Length: " . strlen($cleaned_json) . " characters\n";

    $clean_test = json_decode($cleaned_json, true);
    if ($clean_test === null) {
        echo "❌ Cleaning FAILED - JSON still invalid\n";
        $error_msg = json_last_error_msg();
        $error_code = json_last_error();
        echo "Error after cleaning: $error_msg (code: $error_code)\n\n";

        // Show problematic sections
        echo "=== Problematic JSON Sections ===\n";
        $lines = explode("\n", $template->template_data);
        foreach ($lines as $i => $line) {
            if (strpos($line, '�') !== false || preg_match('/[\x00-\x1F\x7F]/', $line)) {
                echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
            }
        }

        // Offer to replace with default template
        echo "\n=== Repair Options ===\n";
        echo "1. Replace with default template\n";
        echo "2. Show full corrupted JSON for manual repair\n";
        echo "3. Attempt advanced repair\n";

        $choice = readline("Choose option (1-3): ");

        switch ($choice) {
            case '1':
                // Replace with default template
                $default_json = $admin->get_default_template_json();
                $result = $wpdb->update(
                    $table_name,
                    ['template_data' => $default_json],
                    ['id' => 131]
                );
                if ($result !== false) {
                    echo "✅ Template ID 131 replaced with default template\n";
                } else {
                    echo "❌ Failed to update template\n";
                }
                break;

            case '2':
                echo "\n=== Full Corrupted JSON ===\n";
                echo $template->template_data . "\n";
                break;

            case '3':
                // Attempt advanced repair
                echo "Attempting advanced JSON repair...\n";
                $repaired = $admin->advanced_json_repair($template->template_data);
                if ($repaired) {
                    $test = json_decode($repaired, true);
                    if ($test !== null) {
                        $result = $wpdb->update(
                            $table_name,
                            ['template_data' => $repaired],
                            ['id' => 131]
                        );
                        if ($result !== false) {
                            echo "✅ Template ID 131 repaired successfully\n";
                        } else {
                            echo "❌ Failed to update template\n";
                        }
                    } else {
                        echo "❌ Advanced repair failed\n";
                    }
                } else {
                    echo "❌ Advanced repair failed\n";
                }
                break;
        }

    } else {
        echo "✅ Cleaning SUCCESSFUL - JSON is now valid\n";
        // Update the database with cleaned JSON
        $result = $wpdb->update(
            $table_name,
            ['template_data' => $cleaned_json],
            ['id' => 131]
        );
        if ($result !== false) {
            echo "✅ Template ID 131 updated with cleaned JSON\n";
        } else {
            echo "❌ Failed to update template\n";
        }
    }

} else {
    echo "✅ JSON is VALID\n";
    echo "Template structure appears correct.\n";
}

echo "\n=== Diagnostic Complete ===\n";

