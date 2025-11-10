<?php
$content = file_get_contents('plugin/src/Security/Rate_Limiter.php');

// Rename class to PascalCase
$content = str_replace('class Rate_Limiter', 'class RateLimiter', $content);

// Convert method names from snake_case to camelCase
$content = str_replace('check_rate_limit', 'checkRateLimit', $content);
$content = str_replace('get_client_ip', 'getClientIp', $content);
$content = str_replace('get_request_count', 'getRequestCount', $content);
$content = str_replace('reset_for_ip', 'resetForIp', $content);

file_put_contents('plugin/src/Security/Rate_Limiter.php', $content);
echo "Fixed Rate_Limiter.php\n";