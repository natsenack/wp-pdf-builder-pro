<?php
/**
 * Démonstration des améliorations PDF Builder Pro
 *
 * Montre le support des statuts personnalisés et l'inclusion des frais
 *
 * @package PDF_Builder_Pro
 * @version 1.0
 * @since 5.6
 */

// Simuler différents statuts de commande
echo "🔍 DÉMONSTRATION - Support des Statuts Personnalisés\n";
echo "==================================================\n\n";

// Tester différents statuts
$test_statuses = [
    'completed' => 'Terminée',
    'wc-devis' => 'Devis',
    'quote' => 'Devis',
    'shipped' => 'Expédié',
    'delivered' => 'Livré',
    'custom-status' => 'Statut Personnalisé'
];

echo "Statuts de commande supportés :\n";
foreach ($test_statuses as $status => $expected) {
    // Simuler la fonction get_order_status_label
    $statuses = [
        'pending' => 'En attente',
        'processing' => 'En cours',
        'on-hold' => 'En attente',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'refunded' => 'Remboursée',
        'failed' => 'Échouée',
        'wc-devis' => 'Devis',
        'quote' => 'Devis',
        'quotation' => 'Devis',
        'estimate' => 'Devis',
        'draft' => 'Brouillon',
        'partial' => 'Partiellement payé',
        'shipped' => 'Expédié',
        'delivered' => 'Livré',
        'returned' => 'Retourné',
        'backordered' => 'En rupture de stock'
    ];

    $label = isset($statuses[$status]) ? $statuses[$status] : ucfirst(str_replace('-', ' ', $status));
    $icon = isset($statuses[$status]) ? '✅' : '⚠️';

    echo "  $icon $status → '$label'\n";
}

echo "\n📦 DÉMONSTRATION - Inclusion des Frais de Commande\n";
echo "=================================================\n\n";

// Simuler une commande avec produits et frais (mais PAS frais de port)
$order_subtotal = 999.00 + 59.98; // Produits uniquement
$fees_total = 5.00; // SEULEMENT frais de traitement (pas frais de port)
$total_with_fees = $order_subtotal + $fees_total;
$total_with_fees_and_shipping = $total_with_fees + 15.00; // + frais de port séparés

// Simuler une liste de produits avec frais (mais PAS frais de port)
$items = [
    (object)['name' => 'Ordinateur Portable', 'quantity' => 1, 'total' => '999.00'],
    (object)['name' => 'Souris Gaming', 'quantity' => 2, 'total' => '59.98'],
    (object)['name' => 'Frais de traitement', 'quantity' => 1, 'total' => '5.00'] // SEULEMENT frais de commande
];

echo "Liste des produits et frais (commande uniquement) :\n";
$products_list = [];
foreach ($items as $item) {
    $products_list[] = sprintf(
        '%s (x%d) - %s €',
        $item->name,
        $item->quantity,
        number_format($item->total, 2, ',', ' ')
    );
}

echo implode("\n", $products_list) . "\n";

echo "\n💰 CALCULS FINANCIERS :\n";
echo "  • Sous-total : " . number_format($total_with_fees, 2, ',', ' ') . " € (produits + frais de commande)\n";
echo "  • Frais de port (séparés) : 15,00 €\n";
echo "  • Total avant taxes : " . number_format($total_with_fees_and_shipping, 2, ',', ' ') . " €\n";
echo "  • Total final : " . number_format($total_with_fees_and_shipping + 20.00, 2, ',', ' ') . " € (avec taxes)\n";

echo "\n AVANTAGES :\n";
echo "  • Support automatique des plugins ajoutant des statuts (wc-devis, etc.)\n";
echo "  • Frais de commande inclus directement dans le sous-total\n";
echo "  • Frais de port restent séparés (standards WooCommerce)\n";
echo "  • Formatage uniforme pour tous les types d'items\n";
echo "  • Compatibilité maximale avec les extensions WooCommerce\n";

echo "\n🎯 RÉSULTAT : Système plus flexible et complet !\n";