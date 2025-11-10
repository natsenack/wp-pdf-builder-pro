<?php
/**
 * Fix PSR12 violations in ImageGenerator.php
 * - Fix constructor name
 */

$content = file_get_contents('plugin/generators/ImageGenerator.php');

// Fix constructor name
$content = str_replace('function Construct(', 'function __construct(', $content);

file_put_contents('plugin/generators/ImageGenerator.php', $content);

echo "Fixed ImageGenerator.php\n";