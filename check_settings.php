<?php
// Script pour vérifier les paramètres PDF Builder sauvegardés
global $wpdb;

// Simuler l'environnement WordPress
define('WP_USE_THEMES', false);
require_once('wp-load.php');

$settings = get_option('pdf_builder_settings', []);
echo '=== PARAMÈTRES PDF BUILDER SAUVEGARDÉS ===' . PHP_EOL;
echo 'Nombre total de paramètres: ' . count($settings) . PHP_EOL . PHP_EOL;

if (!empty($settings)) {
    echo 'Paramètres canvas trouvés:' . PHP_EOL;
    foreach ($settings as $key => $value) {
        if (strpos($key, 'pdf_builder_canvas_') === 0) {
            echo '  ' . $key . ' => ' . (is_array($value) ? json_encode($value) : $value) . PHP_EOL;
        }
    }

    echo PHP_EOL . 'Tous les paramètres:' . PHP_EOL;
    foreach ($settings as $key => $value) {
        echo '  ' . $key . ' => ' . (is_array($value) ? json_encode($value) : $value) . PHP_EOL;
    }
} else {
    echo 'AUCUN paramètre trouvé dans pdf_builder_settings' . PHP_EOL;
}