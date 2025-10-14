<?php
/**
 * Script pour lister les commandes WooCommerce disponibles
 */

// Vérifier que WordPress est chargé
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once ABSPATH . 'wp-load.php';
}

echo "<h1>📋 Liste des commandes WooCommerce</h1>";

// Vérifier WooCommerce
if (!class_exists('WooCommerce')) {
    echo "❌ WooCommerce non actif<br>";
    exit;
}

echo "<h2>Commandes récentes (5 dernières)</h2>";

// Récupérer les 5 dernières commandes
$args = array(
    'limit' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
    'return' => 'ids'
);

$order_ids = wc_get_orders($args);

if (empty($order_ids)) {
    echo "❌ Aucune commande trouvée<br>";
    exit;
}

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Numéro</th><th>Statut</th><th>Client</th><th>Total</th><th>Date</th></tr>";

foreach ($order_ids as $order_id) {
    $order = wc_get_order($order_id);
    if ($order) {
        echo "<tr>";
        echo "<td>" . $order->get_id() . "</td>";
        echo "<td>" . $order->get_order_number() . "</td>";
        echo "<td>" . $order->get_status() . "</td>";
        echo "<td>" . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "</td>";
        echo "<td>" . $order->get_total() . " " . $order->get_currency() . "</td>";
        echo "<td>" . $order->get_date_created()->format('Y-m-d H:i:s') . "</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<h2>Utilisation du diagnostic</h2>";
echo "<p>Pour tester la génération PDF, utilisez un des IDs ci-dessus dans le script <code>debug_unknown_error.php</code></p>";
echo "<p>Exemple: Modifier <code>\$order_id = 123;</code> en <code>\$order_id = " . $order_ids[0] . ";</code></p>";