<?php
// Charger WordPress
require_once('plugin/bootstrap.php');

// Maintenant on peut utiliser les fonctions WordPress
$mappings = get_option('pdf_builder_order_status_templates', []);
echo 'Mappings configurés: ' . json_encode($mappings) . PHP_EOL;

if (function_exists('wc_get_order_statuses')) {
    $wc_statuses = wc_get_order_statuses();
    echo 'Statuts WooCommerce: ' . json_encode($wc_statuses) . PHP_EOL;
} else {
    echo 'Fonction wc_get_order_statuses non disponible' . PHP_EOL;
}
?>