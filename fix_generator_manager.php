<?php
/**
 * Fix PSR12 violations in GeneratorManager.php
 * - Fix constructor name
 */

$content = file_get_contents('plugin/generators/GeneratorManager.php');

// Fix constructor name
$content = str_replace('function Construct(', 'function __construct(', $content);

file_put_contents('plugin/generators/GeneratorManager.php', $content);

echo "Fixed GeneratorManager.php\n";