<?php
/**
 * Fix PSR12 violations in PDF_Builder_Performance_Monitor.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Performance_Monitor.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Performance_Monitor', 'class PdfBuilderPerformanceMonitor', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'start_ajax_tracking' => 'startAjaxTracking',
    'end_ajax_tracking' => 'endAjaxTracking',
    'track_admin_page_performance' => 'trackAdminPagePerformance',
    'track_frontend_performance' => 'trackFrontendPerformance',
    'log_performance_metric' => 'logPerformanceMetric',
    'get_recent_metrics' => 'getRecentMetrics',
    'get_performance_stats' => 'getPerformanceStats',
    'cleanup_old_logs' => 'cleanupOldLogs'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Performance_Monitor::$old\(/", "PdfBuilderPerformanceMonitor::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Performance_Monitor.php', $content);

echo "Fixed PDF_Builder_Performance_Monitor.php\n";