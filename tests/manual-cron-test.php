<?php
/**
 * Test script to manually trigger cron tasks
 * This will help us see if the cron callbacks work
 */

// Simulate WordPress environment
define('ABSPATH', dirname(__DIR__) . '/plugin/');
define('WPINC', 'wp-includes');

// Load bootstrap
require_once ABSPATH . 'bootstrap.php';

// Test 1: Check if Task Scheduler is loaded
echo "Test 1: Task Scheduler availability\n";
if (class_exists('PDF_Builder_Task_Scheduler')) {
    echo "[PASS] Task Scheduler class exists\n";

    $scheduler = PDF_Builder_Task_Scheduler::get_instance();
    if ($scheduler) {
        echo "[PASS] Task Scheduler instance created\n";

        // Test 2: Manually trigger auto backup
        echo "\nTest 2: Manual auto backup trigger\n";
        try {
            $scheduler->create_auto_backup();
            echo "[PASS] Auto backup method executed\n";
        } catch (Exception $e) {
            echo "[ERROR] Auto backup failed: " . $e->getMessage() . "\n";
        }

        // Test 3: Check scheduled tasks
        echo "\nTest 3: Check scheduled tasks\n";
        $status = $scheduler->get_tasks_status();
        echo "Tasks status: " . json_encode($status, JSON_PRETTY_PRINT) . "\n";

    } else {
        echo "[FAIL] Could not get Task Scheduler instance\n";
    }
} else {
    echo "[FAIL] Task Scheduler class not found\n";
}

echo "\n=== Test Complete ===\n";
?>