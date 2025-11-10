<?php
/**
 * Fix PSR12 violations in PDF_Builder_Path_Validator.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Core/PDF_Builder_Path_Validator.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Core;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Path_Validator', 'class PdfBuilderPathValidator', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'validate_file_path' => 'validateFilePath',
    'contains_directory_traversal' => 'containsDirectoryTraversal',
    'is_absolute_path' => 'isAbsolutePath',
    'is_safe_absolute_path' => 'isSafeAbsolutePath',
    'has_allowed_extension' => 'hasAllowedExtension',
    'is_valid_data_url' => 'isValidDataUrl',
    'is_in_allowed_directory' => 'isInAllowedDirectory',
    'build_safe_path' => 'buildSafePath',
    'get_client_ip' => 'getClientIp',
    'log_security_event' => 'logSecurityEvent'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Path_Validator::$old\(/", "PdfBuilderPathValidator::$new(", $content);
}

file_put_contents('plugin/src/Core/PDF_Builder_Path_Validator.php', $content);

echo "Fixed PDF_Builder_Path_Validator.php\n";