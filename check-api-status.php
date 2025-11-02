<?php
/**
 * Vérification rapide de l'état de l'API Preview
 */

define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'wp-load.php';

$active = get_option('pdf_builder_preview_api_active', false);
echo "API Preview active: " . ($active ? 'OUI' : 'NON') . "\n";

$class_exists = class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI');
echo "Classe PreviewImageAPI: " . ($class_exists ? 'LOADED' : 'NOT FOUND') . "\n";

$files = [
    'assets/js/dist/pdf-preview-api-client.js',
    'assets/js/dist/pdf-preview-integration.js'
];

foreach ($files as $file) {
    $path = plugin_dir_path(dirname(__FILE__)) . 'plugin/' . $file;
    echo "Fichier $file: " . (file_exists($path) ? 'EXISTS' : 'MISSING') . "\n";
}