<?php
/**
 * Fix PSR12 violations in PDF_Builder_Template_Manager.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Template_Manager.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Template_Manager', 'class PdfBuilderTemplateManager', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'init_hooks' => 'initHooks',
    'templates_page' => 'templatesPage',
    'ajax_save_template' => 'ajaxSaveTemplate',
    'ajax_load_template' => 'ajaxLoadTemplate',
    'ajax_auto_save_template' => 'ajaxAutoSaveTemplate',
    'ajax_flush_rest_cache' => 'ajaxFlushRestCache',
    'load_template_robust' => 'loadTemplateRobust',
    'validate_template_structure' => 'validateTemplateStructure',
    'validate_template_element' => 'validateTemplateElement'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Template_Manager::$old\(/", "PdfBuilderTemplateManager::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Template_Manager.php', $content);

echo "Fixed PDF_Builder_Template_Manager.php\n";