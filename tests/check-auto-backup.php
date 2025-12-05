<?php
/**
 * Script to check auto backup settings and status
 */

// Simulate WordPress environment
define('ABSPATH', dirname(__DIR__) . '/plugin/');
define('WPINC', 'wp-includes');

// Load minimal WordPress functions
require_once ABSPATH . 'bootstrap.php';

// Check auto backup settings
echo "=== Auto Backup Status Check ===\n\n";

echo "1. Auto backup enabled: " . (get_option('pdf_builder_auto_backup_enabled', '0') ? 'YES' : 'NO') . "\n";
echo "2. Auto backup frequency: " . get_option('pdf_builder_auto_backup_frequency', 'daily') . "\n";
echo "3. Last auto backup timestamp: " . get_option('pdf_builder_last_auto_backup', '0') . "\n";

$last_backup = get_option('pdf_builder_last_auto_backup', 0);
if ($last_backup > 0) {
    $last_backup_date = date('Y-m-d H:i:s', $last_backup);
    $minutes_ago = round((time() - $last_backup) / 60, 1);
    echo "4. Last auto backup date: $last_backup_date ($minutes_ago minutes ago)\n";
} else {
    echo "4. Last auto backup: NEVER\n";
}

// Check if backup is in progress
$in_progress = get_transient('pdf_builder_auto_backup_in_progress');
echo "5. Backup in progress: " . ($in_progress ? 'YES' : 'NO') . "\n";

// Check scheduled tasks
echo "\n6. Scheduled tasks:\n";
$tasks = [
    'pdf_builder_auto_backup',
    'pdf_builder_cache_cleanup',
    'pdf_builder_log_rotation'
];

foreach ($tasks as $task) {
    $next = wp_next_scheduled($task);
    if ($next) {
        $next_date = date('Y-m-d H:i:s', $next);
        echo "   - $task: scheduled for $next_date\n";
    } else {
        echo "   - $task: NOT scheduled\n";
    }
}

echo "\n=== Check Complete ===\n";
?>