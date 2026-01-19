<?php
// Définir ABSPATH pour éviter la protection
if (!defined('ABSPATH')) {
    define('ABSPATH', 'I:/wp-pdf-builder-pro-V2/');
}

require_once 'I:/wp-pdf-builder-pro-V2/plugin/bootstrap.php';
$settings = pdf_builder_get_option('pdf_builder_settings', []);
echo 'Paramètres canvas sauvegardés:' . PHP_EOL;
echo 'border_color: ' . ($settings['pdf_builder_canvas_border_color'] ?? 'NON DEFINI') . PHP_EOL;
echo 'border_width: ' . ($settings['pdf_builder_canvas_border_width'] ?? 'NON DEFINI') . PHP_EOL;

// Test des valeurs individuelles
echo PHP_EOL . 'Test valeurs individuelles:' . PHP_EOL;
echo 'border_color direct: ' . pdf_builder_get_option('pdf_builder_canvas_border_color', 'DEFAULT') . PHP_EOL;
echo 'border_width direct: ' . pdf_builder_get_option('pdf_builder_canvas_border_width', 'DEFAULT') . PHP_EOL;
?>