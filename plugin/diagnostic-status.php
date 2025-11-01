<?php
/**
 * Script de diagnostic pour les statuts WooCommerce
 * À exécuter sur le serveur WordPress pour vérifier les statuts disponibles
 */

// Vérifier si WooCommerce est chargé
if (!function_exists('wc_get_order_statuses')) {
    echo "ERREUR: WooCommerce n'est pas chargé ou la fonction wc_get_order_statuses n'existe pas.\n";
    exit(1);
}

echo "=== DIAGNOSTIC STATUTS WOO COMMERCE ===\n\n";

// 1. Lister tous les statuts enregistrés
echo "1. Statuts WooCommerce enregistrés :\n";
$statuses = wc_get_order_statuses();
if (empty($statuses)) {
    echo "   AUCUN STATUT TROUVÉ !\n";
} else {
    foreach ($statuses as $key => $label) {
        echo "   $key => $label\n";
    }
}

echo "\n";

// 2. Tester différents statuts
echo "2. Test de validation des statuts :\n";
$test_statuses = ['pending', 'processing', 'completed', 'devis', 'quote', 'wc-devis', 'wc-quote'];

foreach ($test_statuses as $status) {
    $valid_statuses = array_keys(wc_get_order_statuses());
    $status_with_prefix = 'wc-' . $status;
    $status_without_prefix = str_replace('wc-', '', $status);

    $is_valid = in_array($status, $valid_statuses) ||
               in_array($status_with_prefix, $valid_statuses) ||
               in_array($status_without_prefix, $valid_statuses);

    echo "   '$status' => " . ($is_valid ? "VALID" : "INVALID") . "\n";
}

echo "\n";

// 3. Vérifier si des plugins ajoutent des statuts
echo "3. Plugins actifs qui pourraient ajouter des statuts :\n";
if (function_exists('get_option')) {
    $active_plugins = get_option('active_plugins', []);
    $status_plugins = [];

    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'quote') !== false ||
            strpos($plugin, 'devis') !== false ||
            strpos($plugin, 'status') !== false ||
            strpos($plugin, 'order') !== false) {
            $status_plugins[] = $plugin;
        }
    }

    if (empty($status_plugins)) {
        echo "   Aucun plugin suspect détecté\n";
    } else {
        foreach ($status_plugins as $plugin) {
            echo "   - $plugin\n";
        }
    }
} else {
    echo "   Impossible de vérifier les plugins actifs\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";
?>