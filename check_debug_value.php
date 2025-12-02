<?php
// Script de vérification de la valeur debug_javascript en base de données
require_once __DIR__ . '/plugin/pdf-builder-pro.php';

$value = get_option('pdf_builder_debug_javascript', 'NOT_FOUND');
echo "Valeur actuelle de pdf_builder_debug_javascript en BDD: " . $value . "\n";

$all_options = get_option('pdf_builder_settings', []);
echo "Valeur dans pdf_builder_settings: " . ($all_options['pdf_builder_debug_javascript'] ?? 'NOT_FOUND_IN_SETTINGS') . "\n";

echo "Toutes les options debug dans settings:\n";
foreach ($all_options as $key => $val) {
    if (strpos($key, 'debug') !== false) {
        echo "  $key => $val\n";
    }
}
?>