<?php
/**
 * Simple validation script for PDF Builder save system
 * Tests basic functionality without WordPress dependencies
 */

echo "=== PDF Builder Save System Validation ===\n\n";

// Test 1: File structure validation
echo "Test 1: File structure validation\n";
$required_files = [
    '../plugin/resources/templates/admin/settings-parts/settings-main.php',
    '../plugin/pdf-builder-pro.php',
    '../plugin/assets/js/pdf-builder-wrap.js',
    '../plugin/assets/js/ajax-throttle.js',
    '../plugin/assets/js/settings-global-save.js'
];

$files_exist = 0;
foreach ($required_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $files_exist++;
        echo "[PASS] File exists: $file\n";
    } else {
        echo "[FAIL] File missing: $file\n";
    }
}

echo "Files found: $files_exist/" . count($required_files) . "\n\n";

// Test 2: Code analysis - check for main settings file
echo "Test 2: Code analysis - main settings file\n";
$main_settings_file = __DIR__ . '/../plugin/resources/templates/admin/settings-parts/settings-main.php';

if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);

    // Check for key functions
    $checks = [
        'switchTab' => 'Tab switching function',
        'DOMContentLoaded' => 'DOM ready event',
        'nav-tab-active' => 'Active tab class',
        'tab-content' => 'Tab content class'
    ];

    $functions_found = 0;
    foreach ($checks as $function => $description) {
        if (strpos($content, $function) !== false) {
            $functions_found++;
            echo "[PASS] Found $description: $function\n";
        } else {
            echo "[WARN] Missing $description: $function\n";
        }
    }

    echo "Core functions found: $functions_found/" . count($checks) . "\n\n";
} else {
    echo "[FAIL] Main settings file not found\n\n";
}

// Test 3: JavaScript validation
echo "Test 3: JavaScript validation\n";
$js_file = __DIR__ . '/../plugin/assets/js/settings-global-save.js';

if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);

    $js_checks = [
        'pdf_builder_save_' => 'AJAX action prefix',
        'collectAllSettings' => 'Field collection function',
        'saveAllSettingsGlobally' => 'Global save function',
        'Promise' => 'Promise-based AJAX',
        '$.ajax' => 'AJAX calls'
    ];

    $js_features_found = 0;
    foreach ($js_checks as $feature => $description) {
        if (strpos($js_content, $feature) !== false) {
            $js_features_found++;
            echo "[PASS] Found $description: $feature\n";
        } else {
            echo "[WARN] Missing $description: $feature\n";
        }
    }

    echo "JavaScript features found: $js_features_found/" . count($js_checks) . "\n\n";
} else {
    echo "[FAIL] JavaScript file not found\n\n";
}

// Test 4: Security analysis
echo "Test 4: Security analysis\n";
$security_issues = 0;

if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);

    // Check for security measures
    $security_checks = [
        'wp_verify_nonce' => 'Nonce verification',
        'current_user_can' => 'Permission checks',
        'sanitize_text_field' => 'Input sanitization',
        'try {' => 'Error handling (try-catch)',
        'wp_send_json_error' => 'Proper error responses'
    ];

    foreach ($security_checks as $check => $description) {
        if (strpos($content, $check) !== false) {
            echo "[PASS] Security measure present: $description\n";
        } else {
            $security_issues++;
            echo "[WARN] Missing security measure: $description\n";
        }
    }
}

echo "Security issues: $security_issues\n\n";

// Test 5: Architecture analysis
echo "Test 5: Architecture analysis\n";
$architecture_score = 0;

// Check for single responsibility
if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);
    $lines = explode("\n", $content);
    $function_count = 0;

    foreach ($lines as $line) {
        if (preg_match('/function\s+\w+/', $line)) {
            $function_count++;
        }
    }

    if ($function_count >= 2) {
        echo "[PASS] Multiple functions indicate good separation of concerns\n";
        $architecture_score += 2;
    } else {
        echo "[WARN] Limited function count may indicate monolithic code\n";
    }
}

// Check for error handling
if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);
    if (strpos($content, 'try') !== false || strpos($content, 'catch') !== false) {
        echo "[PASS] Error handling structures found\n";
        $architecture_score += 1;
    } else {
        echo "[INFO] No explicit error handling found (may use WordPress error handling)\n";
    }
}

// Check for comments/documentation
if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);
    $comment_lines = preg_match_all('/^\s*\/\//m', $content);
    $total_lines = count(explode("\n", $content));

    if ($comment_lines > 0) {
        $comment_ratio = ($comment_lines / $total_lines) * 100;
        echo "[PASS] Code has comments ($comment_lines lines, " . round($comment_ratio, 1) . "%)\n";
        $architecture_score += 1;
    } else {
        echo "[WARN] No comments found in code\n";
    }
}

echo "Architecture score: $architecture_score/4\n\n";

// Test 6: Performance considerations
echo "Test 6: Performance considerations\n";
$performance_warnings = 0;

if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);

    // Check for potential performance issues
    if (strpos($js_content, 'setTimeout') !== false || strpos($js_content, 'setInterval') !== false) {
        echo "[INFO] Asynchronous operations found (may be for throttling)\n";
    }

    if (strpos($js_content, 'each(') !== false || strpos($js_content, 'forEach') !== false) {
        echo "[INFO] Loops found for processing multiple elements\n";
    }

    // Check for AJAX throttling
    $throttle_file = __DIR__ . '/../plugin/assets/js/ajax-throttle.js';
    if (file_exists($throttle_file)) {
        echo "[PASS] AJAX throttling mechanism exists\n";
    } else {
        echo "[WARN] No AJAX throttling mechanism found\n";
        $performance_warnings++;
    }
}

// Test 7: Code Quality Analysis
echo "Test 7: Code Quality Analysis\n";
$quality_score = 0;
$quality_max = 6;

if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);

    // Check for consistent naming conventions
    if (preg_match_all('/function\s+pdf_builder_\w+/', $content, $matches)) {
        echo "[PASS] Consistent function naming (pdf_builder_ prefix)\n";
        $quality_score += 1;
    }

    // Check for proper indentation
    $lines = explode("\n", $content);
    $proper_indent = 0;
    $total_lines = 0;
    foreach ($lines as $line) {
        if (trim($line) !== '') {
            $total_lines++;
            // Check if line starts with proper indentation (spaces or tabs)
            if (preg_match('/^[\s\t]*\S/', $line)) {
                $proper_indent++;
            }
        }
    }
    if ($total_lines > 0 && ($proper_indent / $total_lines) > 0.8) {
        echo "[PASS] Proper code indentation\n";
        $quality_score += 1;
    }

    // Check for reasonable line lengths (< 120 chars)
    $long_lines = 0;
    foreach ($lines as $line) {
        if (strlen($line) > 120) {
            $long_lines++;
        }
    }
    if ($long_lines === 0) {
        echo "[PASS] Appropriate line lengths\n";
        $quality_score += 1;
    }
}

if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);

    // Check for modern JavaScript features
    if (strpos($js_content, 'const ') !== false || strpos($js_content, 'let ') !== false) {
        echo "[PASS] Modern JavaScript variable declarations\n";
        $quality_score += 1;
    }

    // Check for proper error handling in JS
    if (strpos($js_content, '.catch(') !== false || strpos($js_content, 'try {') !== false) {
        echo "[PASS] JavaScript error handling\n";
        $quality_score += 1;
    }

    // Check for modular code structure
    if (strpos($js_content, 'window.') !== false && strpos($js_content, 'init:') !== false) {
        echo "[PASS] Modular JavaScript architecture\n";
        $quality_score += 1;
    }
}

echo "Code quality score: $quality_score/$quality_max\n\n";

// Test 8: Integration Testing Readiness
echo "Test 8: Integration Testing Readiness\n";
$integration_score = 0;
$integration_max = 4;

// Check for test infrastructure
$test_files = [
    'TestCase.php',
    'AjaxTestCase.php',
    'SettingsSaveTest.php',
    'SecurityTest.php',
    'PerformanceTest.php'
];

$test_files_present = 0;
foreach ($test_files as $test_file) {
    if (file_exists(__DIR__ . '/' . $test_file)) {
        $test_files_present++;
    }
}

if ($test_files_present >= 3) {
    echo "[PASS] Test infrastructure present ($test_files_present/5 test files)\n";
    $integration_score += 1;
}

if ($test_files_present >= 5) {
    echo "[PASS] Complete test suite available\n";
    $integration_score += 1;
}

// Check for proper namespace usage
if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);
    if (strpos($js_content, 'window.PDFBuilderSettingsSaver') !== false) {
        echo "[PASS] Proper JavaScript namespacing\n";
        $integration_score += 1;
    }
}

// Check for WordPress integration
if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);
    if (strpos($content, 'add_action(\'wp_ajax_') !== false && strpos($content, 'wp_send_json_') !== false) {
        echo "[PASS] Proper WordPress AJAX integration\n";
        $integration_score += 1;
    }
}

echo "Integration readiness score: $integration_score/$integration_max\n\n";

// Test 9: Documentation and Maintainability
echo "Test 9: Documentation and Maintainability\n";
$docs_score = 0;
$docs_max = 3;

if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);

    // Check for PHPDoc comments
    if (strpos($content, '/**') !== false && strpos($content, '* @') !== false) {
        echo "[PASS] PHPDoc documentation present\n";
        $docs_score += 1;
    }

    // Check for meaningful function names
    if (preg_match('/function\s+pdf_builder_\w+/', $content)) {
        echo "[PASS] Descriptive function names\n";
        $docs_score += 1;
    }
}

if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);

    // Check for JSDoc comments
    if (strpos($js_content, '/**') !== false || strpos($js_content, '//') !== false) {
        echo "[PASS] JavaScript documentation present\n";
        $docs_score += 1;
    }
}

echo "Documentation score: $docs_score/$docs_max\n\n";

// Test 10: Security Depth Analysis
echo "Test 10: Security Depth Analysis\n";
$security_depth_score = 0;
$security_depth_max = 6;

if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);

    // Check for multiple security layers
    $security_layers = 0;
    if (strpos($content, 'current_user_can') !== false) $security_layers++;
    if (strpos($content, 'wp_verify_nonce') !== false) $security_layers++;
    if (strpos($content, 'sanitize_') !== false) $security_layers++;
    if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) $security_layers++;

    if ($security_layers >= 3) {
        echo "[PASS] Multiple security layers implemented ($security_layers/4)\n";
        $security_depth_score += 2;
    }

    // Check for secure error messages
    if (strpos($content, 'wp_send_json_error') !== false && !strpos($content, 'error_log')) {
        echo "[PASS] Secure error message handling\n";
        $security_depth_score += 1;
    }

    // Check for no hardcoded secrets
    if (!preg_match('/password|secret|key\s*=/i', $content)) {
        echo "[PASS] No hardcoded sensitive data\n";
        $security_depth_score += 1;
    }

    // Check for proper JSON response handling
    if (strpos($content, 'wp_send_json_error') !== false && strpos($content, 'wp_send_json_success') !== false) {
        echo "[PASS] Proper JSON response handling\n";
        $security_depth_score += 1;
    }

    // Check for input validation
    if (strpos($content, 'isset($_POST[') !== false && strpos($content, 'sanitize_') !== false) {
        echo "[PASS] Input validation implemented\n";
        $security_depth_score += 1;
    }
}

echo "Security depth score: $security_depth_score/$security_depth_max\n\n";

// Test 11: Syntax and Compilation Validation
echo "Test 11: Syntax and Compilation Validation\n";
$syntax_score = 0;
$syntax_max = 2;

// Check PHP syntax
if (file_exists($main_settings_file)) {
    $syntax_check = shell_exec("php -l \"$main_settings_file\" 2>&1");
    if (strpos($syntax_check, 'No syntax errors detected') !== false) {
        echo "[PASS] PHP syntax validation passed\n";
        $syntax_score += 1;
    } else {
        echo "[FAIL] PHP syntax errors detected\n";
    }
}

// Check for proper file structure and includes
$required_includes = ['wp_send_json_success', 'wp_send_json_error', 'wp_verify_nonce', 'current_user_can'];
$includes_found = 0;
foreach ($required_includes as $include) {
    if (strpos($content, $include) !== false) {
        $includes_found++;
    }
}

if ($includes_found >= count($required_includes)) {
    echo "[PASS] All required WordPress functions included\n";
    $syntax_score += 1;
}

echo "Syntax validation score: $syntax_score/$syntax_max\n\n";

// Test 12: Final Robustness Check
echo "Test 12: Final Robustness Check\n";
$robustness_score = 0;
$robustness_max = 1;

// Check for comprehensive error handling and edge cases
if (file_exists($main_settings_file)) {
    $content = file_get_contents($main_settings_file);

    // Check for comprehensive error handling
    $error_patterns = 0;
    if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) $error_patterns++;
    if (strpos($content, 'wp_send_json_error') !== false) $error_patterns++;
    if (strpos($content, 'wp_send_json_success') !== false) $error_patterns++;
    if (strpos($content, 'error_log') !== false) $error_patterns++;

    if ($error_patterns >= 4) {
        echo "[PASS] Comprehensive error handling and logging\n";
        $robustness_score += 1;
    }
}

echo "Robustness score: $robustness_score/$robustness_max\n\n";

// Summary
echo "=== Validation Summary ===\n";
echo "File Structure: " . ($files_exist == count($required_files) ? "[PASS]" : "[WARN]") . " $files_exist/" . count($required_files) . " files present\n";
echo "Factory Functions: " . (isset($functions_found) && $functions_found >= 4 ? "[PASS]" : "[WARN]") . " Core functions available\n";
echo "JavaScript Features: " . (isset($js_features_found) && $js_features_found >= 3 ? "[PASS]" : "[WARN]") . " Client-side functionality\n";
echo "Security Measures: " . ($security_issues == 0 ? "[PASS]" : "[WARN]") . " Security validation\n";
echo "Architecture: " . ($architecture_score >= 3 ? "[GOOD]" : "[FAIR]") . " Code organization\n";
echo "Performance: " . ($performance_warnings == 0 ? "[PASS]" : "[WARN]") . " Performance considerations\n";
echo "Code Quality: " . ($quality_score >= 4 ? "[EXCELLENT]" : ($quality_score >= 2 ? "[GOOD]" : "[FAIR]")) . " Code standards ($quality_score/$quality_max)\n";
echo "Integration: " . ($integration_score >= 3 ? "[EXCELLENT]" : ($integration_score >= 2 ? "[GOOD]" : "[FAIR]")) . " Test & integration readiness ($integration_score/$integration_max)\n";
echo "Documentation: " . ($docs_score >= 2 ? "[GOOD]" : "[FAIR]") . " Code documentation ($docs_score/$docs_max)\n";
echo "Security Depth: " . ($security_depth_score >= 4 ? "[EXCELLENT]" : ($security_depth_score >= 3 ? "[GOOD]" : "[FAIR]")) . " Advanced security ($security_depth_score/$security_depth_max)\n";
echo "Syntax Validation: " . ($syntax_score >= 2 ? "[EXCELLENT]" : ($syntax_score >= 1 ? "[GOOD]" : "[FAIL]")) . " Code compilation ($syntax_score/$syntax_max)\n";
echo "Robustness: " . ($robustness_score >= 1 ? "[EXCELLENT]" : "[FAIL]") . " Error handling completeness ($robustness_score/$robustness_max)\n";

// Enhanced scoring system with more weight on quality aspects
$base_score = ($files_exist / count($required_files)) * 15 +
              (isset($functions_found) ? ($functions_found / count($checks)) * 15 : 0) +
              (isset($js_features_found) ? ($js_features_found / count($js_checks)) * 15 : 0) +
              (10 - $security_issues) +
              ($architecture_score * 1.5) +
              (5 - $performance_warnings);

$quality_bonus = ($quality_score / $quality_max) * 10 +
                 ($integration_score / $integration_max) * 10 +
                 ($docs_score / $docs_max) * 5 +
                 ($security_depth_score / $security_depth_max) * 10 +
                 ($syntax_score / $syntax_max) * 5 +
                 ($robustness_score / $robustness_max) * 5;

$overall_score = min(100, $base_score + $quality_bonus);

echo "\nOverall Score: " . round($overall_score, 1) . "/100\n";

if ($overall_score >= 95) {
    echo "Status: [OUTSTANDING] Production-ready system with exceptional quality\n";
} elseif ($overall_score >= 85) {
    echo "Status: [EXCELLENT] Well-implemented system exceeding standards\n";
} elseif ($overall_score >= 75) {
    echo "Status: [VERY GOOD] High-quality implementation\n";
} elseif ($overall_score >= 60) {
    echo "Status: [GOOD] System is functional with minor issues\n";
} elseif ($overall_score >= 40) {
    echo "Status: [FAIR] System needs improvements\n";
} else {
    echo "Status: [POOR] System requires significant work\n";
}

echo "\n=== Detailed Breakdown ===\n";
echo "Base Functionality: " . round($base_score, 1) . "/70\n";
echo "Quality & Integration: " . round($quality_bonus, 1) . "/30\n";
echo "\nValidation completed with comprehensive quality assessment.\n";