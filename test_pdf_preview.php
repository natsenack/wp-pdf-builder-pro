<?php
/**
 * Script de test pour diagnostiquer l'erreur AJAX du bouton "aperçu PDF"
 * Placez ce fichier dans le répertoire racine de WordPress et accédez-y via le navigateur
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure WordPress
require_once('wp-load.php');

// Simuler les paramètres d'une commande réelle
$orderId = 123; // Remplacez par un ID de commande réel

echo "<h1>Test de génération d'URL nonce pour aperçu PDF</h1>";

// Générer l'URL comme le fait meta-box.php
$url = wp_nonce_url(
    admin_url("admin-ajax.php?action=rednao_wcpdfinv_generate_pdf&orderid=" . $orderId),
    'rednao_wcpdfinv_generate_pdf_' . $orderId
);

echo "<p><strong>URL générée :</strong></p>";
echo "<code>" . htmlspecialchars($url) . "</code>";

// Tester si l'URL est accessible
echo "<p><strong>Test de l'URL :</strong></p>";
echo "<a href='" . $url . "' target='_blank'>Tester l'URL (s'ouvrira dans un nouvel onglet)</a>";

// Afficher les composants de l'URL
$parsedUrl = parse_url($url);
parse_str($parsedUrl['query'], $params);

echo "<p><strong>Paramètres extraits :</strong></p>";
echo "<ul>";
foreach ($params as $key => $value) {
    echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
}
echo "</ul>";

// Vérifier le nonce
if (isset($params['_wpnonce'])) {
    $nonceValid = wp_verify_nonce($params['_wpnonce'], 'rednao_wcpdfinv_generate_pdf_' . $orderId);
    echo "<p><strong>Nonce valide :</strong> " . ($nonceValid ? 'OUI' : 'NON') . "</p>";
}

// Vérifier si la commande existe
$order = wc_get_order($orderId);
echo "<p><strong>Commande WooCommerce existe :</strong> " . ($order ? 'OUI' : 'NON') . "</p>";

if ($order) {
    echo "<p><strong>Numéro de commande :</strong> " . $order->get_order_number() . "</p>";
    echo "<p><strong>Statut :</strong> " . $order->get_status() . "</p>";
}
?>