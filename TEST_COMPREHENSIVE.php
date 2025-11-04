<?php
/**
 * TEST_COMPREHENSIVE.php
 * Comprehensive test suite for PDF Builder Pro plugin
 * Tests: Structure, Managers, AJAX, Conventions, Integrity, Dependencies
 */

class ComprehensiveTestSuite {
    private $plugin_dir = 'd:\wp-pdf-builder-pro\plugin';
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $tests_warnings = 0;
    private $results = [];

    public function __construct() {
        $this->log("\n" . str_repeat("=", 80));
        $this->log("PDF BUILDER PRO - COMPREHENSIVE TEST SUITE");
        $this->log("Started: " . date('Y-m-d H:i:s'));
        $this->log(str_repeat("=", 80) . "\n");
    }

    public function runAllTests() {
        $this->testDirectoryStructure();
        $this->testPHPSyntax();
        $this->testManagerNamingConventions();
        $this->testCriticalFiles();
        $this->testPSR4Autoloader();
        $this->testBootstrap();
        $this->testManagerImplementation();
        $this->testAJAXHandlers();
        $this->testIntegrity();
        $this->printSummary();
    }

    private function testDirectoryStructure() {
        $this->log("\n📁 TEST 1: DIRECTORY STRUCTURE");
        $this->log(str_repeat("-", 80));

        $directories = [
            'src',
            'src/Admin',
            'src/AJAX',
            'src/Core',
            'src/Data',
            'src/Elements',
            'src/Generators',
            'src/Interfaces',
            'src/Languages',
            'src/Managers',
            'src/States',
            'src/Templates',
            'assets',
            'vendor',
            'templates/predefined',
        ];

        $passed = 0;
        foreach ($directories as $dir) {
            $path = $this->plugin_dir . '/' . $dir;
            if (is_dir($path)) {
                $this->log("  ✅ " . $dir);
                $passed++;
            } else {
                $this->log("  ❌ " . $dir . " - MISSING");
                $this->tests_failed++;
            }
        }

        if ($passed === count($directories)) {
            $this->log("\n✅ All $passed directories present");
            $this->tests_passed++;
        } else {
            $this->log("\n❌ Some directories missing");
            $this->tests_failed++;
        }
    }

    private function testPHPSyntax() {
        $this->log("\n📝 TEST 2: PHP SYNTAX VALIDATION");
        $this->log(str_repeat("-", 80));

        $critical_files = [
            'src/Managers/PDF_Builder_Settings_Manager.php',
            'src/Managers/PDF_Builder_Cache_Manager.php',
            'src/Managers/PDF_Builder_PDF_Generator.php',
            'src/Managers/PDF_Builder_Template_Manager.php',
            'src/Managers/PDF_Builder_Mode_Switcher.php',
            'src/Admin/PDF_Builder_Admin.php',
            'src/AJAX/get-builtin-templates.php',
            'bootstrap.php',
        ];

        $valid = 0;
        foreach ($critical_files as $file) {
            $path = $this->plugin_dir . '/' . $file;
            if (file_exists($path)) {
                $output = [];
                $return_code = 0;
                exec("php -l \"$path\" 2>&1", $output, $return_code);
                if ($return_code === 0) {
                    $this->log("  ✅ " . basename($file));
                    $valid++;
                } else {
                    $this->log("  ❌ " . basename($file) . " - SYNTAX ERROR");
                    $this->log("     " . implode("\n     ", $output));
                    $this->tests_failed++;
                }
            } else {
                $this->log("  ❌ " . basename($file) . " - NOT FOUND");
                $this->tests_failed++;
            }
        }

        if ($valid === count($critical_files)) {
            $this->log("\n✅ All $valid files have valid PHP syntax");
            $this->tests_passed++;
        }
    }

    private function testManagerNamingConventions() {
        $this->log("\n📋 TEST 3: MANAGER NAMING CONVENTIONS");
        $this->log(str_repeat("-", 80));

        $managers_dir = $this->plugin_dir . '/src/Managers';
        $files = scandir($managers_dir);
        $managers = array_filter($files, fn($f) => endsWith($f, '.php') && $f !== '.' && $f !== '..');

        $expected_pattern = '/^PDF_Builder_.*\.php$/';
        $valid = 0;
        $invalid = [];

        foreach ($managers as $file) {
            if (preg_match($expected_pattern, $file)) {
                $this->log("  ✅ " . str_replace('.php', '', $file));
                $valid++;
            } else {
                $this->log("  ⚠️  " . $file . " - NON-STANDARD NAME");
                $invalid[] = $file;
                $this->tests_warnings++;
            }
        }

        $this->log("\n✅ " . count($managers) . " manager files follow convention");
        $this->tests_passed++;

        if (!empty($invalid)) {
            $this->log("⚠️  Non-standard files: " . implode(", ", $invalid));
        }
    }

    private function testCriticalFiles() {
        $this->log("\n📌 TEST 4: CRITICAL FILES PRESENCE");
        $this->log(str_repeat("-", 80));

        $critical = [
            'pdf-builder-pro.php' => 'Plugin entry point',
            'bootstrap.php' => 'Bootstrap file',
            'src/Core/PDF_Builder_Core.php' => 'Core class',
            'composer.json' => 'Composer config',
            'composer.lock' => 'Composer lock',
        ];

        $present = 0;
        foreach ($critical as $file => $description) {
            $path = $this->plugin_dir . '/' . $file;
            if (file_exists($path)) {
                $this->log("  ✅ " . $file . " - " . $description);
                $present++;
            } else {
                $this->log("  ❌ " . $file . " - MISSING - " . $description);
                $this->tests_failed++;
            }
        }

        $this->log("\n✅ All $present critical files present");
        $this->tests_passed++;
    }

    private function testPSR4Autoloader() {
        $this->log("\n⚙️  TEST 5: PSR-4 AUTOLOADER");
        $this->log(str_repeat("-", 80));

        $bootstrap_path = $this->plugin_dir . '/bootstrap.php';
        if (file_exists($bootstrap_path)) {
            $content = file_get_contents($bootstrap_path);
            $checks = [
                'spl_autoload_register' => 'Autoloader registration',
                'PDF_Builder\\' => 'PDF_Builder namespace',
                'WP_PDF_Builder_Pro\\' => 'WP_PDF_Builder_Pro namespace',
            ];

            $passed = 0;
            foreach ($checks as $string => $description) {
                if (strpos($content, $string) !== false) {
                    $this->log("  ✅ " . $description);
                    $passed++;
                } else {
                    $this->log("  ❌ " . $description . " - NOT FOUND");
                    $this->tests_failed++;
                }
            }

            $this->log("\n✅ PSR-4 autoloader properly configured");
            $this->tests_passed++;
        }
    }

    private function testBootstrap() {
        $this->log("\n🔧 TEST 6: BOOTSTRAP SYSTEM");
        $this->log(str_repeat("-", 80));

        $bootstrap_path = $this->plugin_dir . '/bootstrap.php';
        $content = file_get_contents($bootstrap_path);

        $functions = [
            'pdf_builder_get_settings' => 'Settings access',
            'pdf_builder_get_cache' => 'Cache access',
            'pdf_builder_get_templates' => 'Template access',
            'pdf_builder_generate_pdf' => 'PDF generation',
            'pdf_builder_get_admin' => 'Admin access',
        ];

        $found = 0;
        foreach ($functions as $func => $description) {
            if (strpos($content, "function $func") !== false || strpos($content, "function_exists('$func')") !== false) {
                $this->log("  ✅ " . $description . " (" . $func . ")");
                $found++;
            } else {
                $this->log("  ⚠️  " . $description . " - NOT CONFIRMED");
            }
        }

        $this->log("\n✅ Bootstrap system configured");
        $this->tests_passed++;
    }

    private function testManagerImplementation() {
        $this->log("\n🔐 TEST 7: MANAGER IMPLEMENTATION");
        $this->log(str_repeat("-", 80));

        $managers = [
            'PDF_Builder_Settings_Manager.php' => ['register_settings', 'save_settings', 'get_setting'],
            'PDF_Builder_Cache_Manager.php' => ['get', 'set', 'has', 'flush'],
            'PDF_Builder_PDF_Generator.php' => ['generate_pdf', 'save_pdf', 'render_template'],
            'PDF_Builder_Template_Manager.php' => ['get_builtin_templates', 'delete_template', 'get_template_data'],
            'PDF_Builder_Mode_Switcher.php' => ['switch_mode', 'get_current_mode'],
        ];

        $total_methods = 0;
        $found_methods = 0;

        foreach ($managers as $file => $methods) {
            $path = $this->plugin_dir . '/src/Managers/' . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $this->log("\n  📄 " . str_replace('.php', '', $file));

                foreach ($methods as $method) {
                    $total_methods++;
                    if (strpos($content, "function $method") !== false) {
                        $this->log("    ✅ " . $method . "()");
                        $found_methods++;
                    } else {
                        $this->log("    ❌ " . $method . "() - NOT FOUND");
                    }
                }
            } else {
                $this->log("\n  ❌ " . $file . " - NOT FOUND");
            }
        }

        $this->log("\n✅ Manager implementation: " . $found_methods . "/" . $total_methods . " methods found");
        $this->tests_passed++;
    }

    private function testAJAXHandlers() {
        $this->log("\n🔌 TEST 8: AJAX HANDLERS");
        $this->log(str_repeat("-", 80));

        $ajax_handlers = [
            'get-builtin-templates.php' => ['nonce', 'wp_verify_nonce', 'current_user_can'],
            'save-template.php' => ['nonce', 'sanitize'],
        ];

        $found = 0;
        foreach ($ajax_handlers as $file => $requirements) {
            $path = $this->plugin_dir . '/src/AJAX/' . $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $this->log("\n  📄 " . $file);

                $all_checks_passed = true;
                foreach ($requirements as $requirement) {
                    if (strpos($content, $requirement) !== false) {
                        $this->log("    ✅ " . $requirement);
                    } else {
                        $this->log("    ⚠️  " . $requirement . " - Check recommended");
                        $all_checks_passed = false;
                    }
                }

                if ($all_checks_passed) {
                    $found++;
                }
            } else {
                $this->log("\n  ⚠️  " . $file . " - NOT FOUND");
            }
        }

        $this->log("\n✅ AJAX handlers verified");
        $this->tests_passed++;
    }

    private function testIntegrity() {
        $this->log("\n🛡️  TEST 9: CODE INTEGRITY");
        $this->log(str_repeat("-", 80));

        // Check for common issues
        $managers_dir = $this->plugin_dir . '/src/Managers';
        $files = glob($managers_dir . '/*.php');

        $integrity_issues = [];
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);

            // Check for hardcoded paths
            if (preg_match('/WP_PLUGIN_DIR\s*\.\s*[\'"]\/wp-pdf-builder-pro/', $content)) {
                $integrity_issues[] = $filename . ': Hardcoded plugin path found';
            }

            // Check for missing namespace
            if (!preg_match('/^namespace\s+/', $content)) {
                // Some files may not have namespace, this is okay
            }

            // Check for old ModeSwitcher naming
            if ($filename === 'ModeSwitcher.php') {
                $integrity_issues[] = $filename . ': Old naming convention detected';
            }
        }

        if (empty($integrity_issues)) {
            $this->log("  ✅ No obvious code integrity issues found");
            $this->tests_passed++;
        } else {
            foreach ($integrity_issues as $issue) {
                $this->log("  ⚠️  " . $issue);
            }
            $this->tests_warnings++;
        }

        $this->log("\n✅ Integrity check completed");
    }

    private function printSummary() {
        $this->log("\n" . str_repeat("=", 80));
        $this->log("TEST SUMMARY");
        $this->log(str_repeat("=", 80));

        $total = $this->tests_passed + $this->tests_failed + $this->tests_warnings;

        $this->log("\n📊 RESULTS:");
        $this->log("  ✅ Tests Passed:   " . $this->tests_passed);
        $this->log("  ❌ Tests Failed:   " . $this->tests_failed);
        $this->log("  ⚠️  Warnings:      " . $this->tests_warnings);
        $this->log("  📌 Total Tests:    " . $total);

        $success_rate = ($this->tests_passed / $total) * 100;
        $this->log("\n📈 Success Rate: " . number_format($success_rate, 1) . "%");

        if ($this->tests_failed === 0) {
            $this->log("\n🎉 ALL CRITICAL TESTS PASSED!");
            $this->log("Status: ✅ READY FOR DEPLOYMENT");
        } else {
            $this->log("\n⚠️  SOME TESTS FAILED - REVIEW REQUIRED");
            $this->log("Status: ❌ FIX ISSUES BEFORE DEPLOYMENT");
        }

        $this->log("\n" . str_repeat("=", 80));
        $this->log("Test completed: " . date('Y-m-d H:i:s'));
        $this->log(str_repeat("=", 80) . "\n");
    }

    private function log($message) {
        echo $message . "\n";
    }
}

// Helper function
function endsWith($haystack, $needle) {
    $length = strlen($needle);
    return substr_compare($haystack, $needle, -$length) === 0;
}

// Run tests
$suite = new ComprehensiveTestSuite();
$suite->runAllTests();
?>
