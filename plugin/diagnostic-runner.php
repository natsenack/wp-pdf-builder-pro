<?php
/**
 * Point d'entrée pour le diagnostic des statuts WooCommerce
 * À placer à la racine du plugin pour exécution via navigateur
 * Version: 1.0.1
 */

require_once '../../../wp-load.php'; // Charger WordPress

if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

echo "<h1>Diagnostic Statuts WooCommerce</h1>";
echo "<pre>";

// Inclure et exécuter le script de diagnostic
require_once 'diagnostic-status.php';

echo "</pre>";
?>