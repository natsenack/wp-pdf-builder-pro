<?php
/**
 * Fix PSR12 violations in PDF_Builder_Drag_Drop_Manager.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Drag_Drop_Manager.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Drag_Drop_Manager', 'class PdfBuilderDragDropManager', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'init_dependencies' => 'initDependencies',
    'start_drag_session' => 'startDragSession',
    'update_drag_position' => 'updateDragPosition',
    'end_drag_session' => 'endDragSession',
    'get_drag_session' => 'getDragSession',
    'cleanup_drag_session' => 'cleanupDragSession',
    'validate_drag_data' => 'validateDragData',
    'calculate_drag_collisions' => 'calculateDragCollisions',
    'log_drag_event' => 'logDragEvent',
    'get_drag_performance_stats' => 'getDragPerformanceStats'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Drag_Drop_Manager::$old\(/", "PdfBuilderDragDropManager::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Drag_Drop_Manager.php', $content);

echo "Fixed PDF_Builder_Drag_Drop_Manager.php\n";