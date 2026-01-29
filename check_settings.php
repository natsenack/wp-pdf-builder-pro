<?php
require_once('wp-load.php');
$settings = get_option('pdf_builder_settings', array());
echo 'Paramètres actuels:' . PHP_EOL;
foreach ($settings as $key => $value) {
    if (strpos($key, 'format') !== false || strpos($key, 'orientation') !== false || strpos($key, 'dpi') !== false) {
        echo $key . ': ' . (is_array($value) ? implode(', ', $value) : $value) . PHP_EOL;
    }
}
echo PHP_EOL . 'Tous les paramètres:' . PHP_EOL;
print_r($settings);
?>