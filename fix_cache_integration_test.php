<?php
/**
 * Fix PSR12 violations in cache-integration-test.php
 * - Add namespace
 */

$content = file_get_contents('plugin/src/Cache/cache-integration-test.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Cache;\n\n",
    $content
);

file_put_contents('plugin/src/Cache/cache-integration-test.php', $content);

echo "Fixed cache-integration-test.php\n";