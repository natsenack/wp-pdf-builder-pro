<?php
$content = file_get_contents('plugin/src/Security/Security_Limits_Handler.php');

// Rename class to PascalCase
$content = str_replace('class Security_Limits_Handler', 'class SecurityLimitsHandler', $content);

// Convert method names from snake_case to camelCase
$content = str_replace('apply_security_limits', 'applySecurityLimits', $content);
$content = str_replace('validate_upload_size', 'validateUploadSize', $content);
$content = str_replace('validate_template_size', 'validateTemplateSize', $content);
$content = str_replace('get_limits_info', 'getLimitsInfo', $content);

file_put_contents('plugin/src/Security/Security_Limits_Handler.php', $content);
echo "Fixed Security_Limits_Handler.php\n";