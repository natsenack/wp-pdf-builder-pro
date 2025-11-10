<?php
$content = file_get_contents('plugin/src/License/license-expiration-handler.php');

// Rename class to PascalCase
$content = str_replace('class License_Expiration_Handler', 'class LicenseExpirationHandler', $content);

// Convert method names from snake_case to camelCase
$content = str_replace('schedule_expiration_check', 'scheduleExpirationCheck', $content);
$content = str_replace('check_license_expiration', 'checkLicenseExpiration', $content);
$content = str_replace('send_expiration_notification', 'sendExpirationNotification', $content);
$content = str_replace('clear_scheduled_expiration_check', 'clearScheduledExpirationCheck', $content);

file_put_contents('plugin/src/License/license-expiration-handler.php', $content);
echo "Fixed license-expiration-handler.php\n";