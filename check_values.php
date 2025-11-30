<?php
// Script de vérification des valeurs sauvegardées
require_once('../wp-load.php');

// Liste des options développeur à vérifier
$dev_options = [
    'pdf_builder_developer_enabled',
    'pdf_builder_debug_php_errors',
    'pdf_builder_debug_javascript',
    'pdf_builder_debug_javascript_verbose',
    'pdf_builder_debug_ajax',
    'pdf_builder_debug_performance',
    'pdf_builder_debug_database',
    'pdf_builder_debug_pdf_editor'
];

echo "=== VÉRIFICATION DES VALEURS SAUVEGARDÉES ===\n\n";

foreach ($dev_options as $option) {
    $value = get_option($option, 'NOT_SET');
    $type = gettype($value);
    echo sprintf("%-35s : %-10s (%s)\n", $option, var_export($value, true), $type);
}

echo "\n=== FIN DE LA VÉRIFICATION ===\n";
?>