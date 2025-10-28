<?php
define('ABSPATH', dirname(__FILE__) . '/');
define('WP_DEBUG', true);
require_once 'wp-load.php';

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';
$template = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table_templates . ' WHERE id = %d', 1), ARRAY_A);

if ($template) {
    echo 'Template ID 1 trouvé:' . PHP_EOL;
    echo 'Nom: ' . $template['name'] . PHP_EOL;
    echo 'Créé: ' . $template['created_at'] . PHP_EOL;
    echo 'Modifié: ' . $template['updated_at'] . PHP_EOL;
    echo 'Données JSON (longueur): ' . strlen($template['template_data']) . ' caractères' . PHP_EOL;

    $data = json_decode($template['template_data'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo 'JSON valide' . PHP_EOL;
        if (isset($data['elements'])) {
            echo 'Nombre d\'éléments: ' . count($data['elements']) . PHP_EOL;
        } else {
            echo 'Pas d\'éléments trouvés dans les données' . PHP_EOL;
        }
    } else {
        echo 'Erreur JSON: ' . json_last_error_msg() . PHP_EOL;
    }
} else {
    echo 'Template ID 1 non trouvé' . PHP_EOL;
}