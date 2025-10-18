<?php
echo "=== PHP Debug Test ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "ZTS: " . (PHP_ZTS ? 'Yes' : 'No') . "\n";
echo "Architecture: " . php_uname('m') . "\n";
echo "Extensions loaded: " . count(get_loaded_extensions()) . "\n";

echo "\n=== Testing basic functionality ===\n";
$x = 42;
$y = $x * 2;
echo "Math test: 42 * 2 = " . $y . "\n";

echo "\n=== Testing WordPress compatibility ===\n";
if (function_exists('wp_die')) {
    echo "WordPress functions available\n";
} else {
    echo "WordPress not loaded (expected in test environment)\n";
}

echo "\n=== Configuration check ===\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";

echo "\n✅ PHP is working correctly!\n";
?>