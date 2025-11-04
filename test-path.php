<?php
$manager_dir = 'd:\wp-pdf-builder-pro\plugin\src\Managers';
$src_dir = dirname($manager_dir);
$plugin_dir = dirname($src_dir);
$builtin_dir = $plugin_dir . '/templates/builtin/';

echo 'manager_dir: ' . $manager_dir . PHP_EOL;
echo 'src_dir: ' . $src_dir . PHP_EOL;
echo 'plugin_dir: ' . $plugin_dir . PHP_EOL;
echo 'builtin_dir: ' . $builtin_dir . PHP_EOL;
echo 'exists: ' . (is_dir($builtin_dir) ? 'YES' : 'NO') . PHP_EOL;

if (is_dir($builtin_dir)) {
    $files = glob($builtin_dir . '*.json');
    echo 'files found: ' . count($files) . PHP_EOL;
    foreach ($files as $f) {
        echo '  - ' . basename($f) . PHP_EOL;
    }
}
?>
