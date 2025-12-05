<?php
/**
 * Manual test script for PDF Builder Task Scheduler
 * Tests if cron tasks execute properly after fixing multiple instantiation
 */

echo "=== PDF Builder Task Scheduler Manual Tests ===\n\n";

// Simulate WordPress environment
define('ABSPATH', dirname(__DIR__) . '/plugin/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', dirname(__DIR__) . '/plugin');

// Load WordPress core functions (minimal)
require_once ABSPATH . 'bootstrap.php';

// Test 1: Check if Task Scheduler loads without multiple instantiation
echo "Test 1: Task Scheduler instantiation\n";
try {
    $scheduler1 = PDF_Builder_Task_Scheduler::get_instance();
    $scheduler2 = PDF_Builder_Task_Scheduler::get_instance();

    if ($scheduler1 === $scheduler2) {
        echo "[PASS] Singleton pattern working - same instance returned\n";
    } else {
        echo "[FAIL] Singleton pattern broken - different instances returned\n";
    }
} catch (Exception $e) {
    echo "[ERROR] Task Scheduler instantiation failed: " . $e->getMessage() . "\n";
}

// Test 2: Check if cron tasks are scheduled
echo "\nTest 2: Cron tasks scheduling\n";
if (method_exists($scheduler1, 'get_tasks_status')) {
    $status = $scheduler1->get_tasks_status();
    echo "Tasks status: " . json_encode($status, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "[FAIL] get_tasks_status method not available\n";
}

// Test 3: Test backup creation manually
echo "\nTest 3: Manual backup creation test\n";
if (method_exists($scheduler1, 'create_auto_backup')) {
    try {
        $result = $scheduler1->create_auto_backup();
        if ($result) {
            echo "[PASS] Manual backup creation successful\n";
        } else {
            echo "[FAIL] Manual backup creation failed\n";
        }
    } catch (Exception $e) {
        echo "[ERROR] Manual backup creation error: " . $e->getMessage() . "\n";
    }
} else {
    echo "[FAIL] create_auto_backup method not available\n";
}

echo "\n=== Test Complete ===\n";
?>