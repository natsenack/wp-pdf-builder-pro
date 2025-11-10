<?php
/**
 * Fix PSR12 violations in Canvas_AJAX_Handler.php
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Admin/Canvas_AJAX_Handler.php');

// Rename class
$content = str_replace('class Canvas_AJAX_Handler', 'class CanvasAjaxHandler', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'register_hooks' => 'registerHooks',
    'get_canvas_settings' => 'getCanvasSettings',
    'save_canvas_settings' => 'saveCanvasSettings',
    'reset_canvas_settings' => 'resetCanvasSettings'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/Canvas_AJAX_Handler::$old\(/", "CanvasAjaxHandler::$new(", $content);
}

file_put_contents('plugin/src/Admin/Canvas_AJAX_Handler.php', $content);

echo "Fixed Canvas_AJAX_Handler.php\n";