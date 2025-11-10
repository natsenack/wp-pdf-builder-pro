<?php
/**
 * Fix PSR12 violations in PDF_Builder_Security_Validator.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Core/PDF_Builder_Security_Validator.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Core;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Security_Validator', 'class PdfBuilderSecurityValidator', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'sanitize_html_content' => 'sanitizeHtmlContent',
    'validate_json_data' => 'validateJsonData',
    'sanitize_array_data' => 'sanitizeArrayData',
    'validate_nonce' => 'validateNonce',
    'get_client_ip' => 'getClientIp',
    'log_security_event' => 'logSecurityEvent',
    'check_permissions' => 'checkPermissions'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Security_Validator::$old\(/", "PdfBuilderSecurityValidator::$new(", $content);
}

file_put_contents('plugin/src/Core/PDF_Builder_Security_Validator.php', $content);

echo "Fixed PDF_Builder_Security_Validator.php\n";