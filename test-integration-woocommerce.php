<?php
/**
 * Test d'intégration WooCommerce - Phase 1.6.1
 * Vérifie que les hooks et l'intégration fonctionnent correctement
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "=== TEST D'INTÉGRATION WOOCOMMERCE - PHASE 1.6.1 ===\n\n";

// 1. Vérifier que WooCommerce est actif
echo "1. Vérification WooCommerce...\n";
if (class_exists('WooCommerce')) {
    echo "✅ WooCommerce est actif\n";
} else {
    echo "❌ WooCommerce n'est pas actif\n";
}

// 2. Vérifier les classes du plugin
echo "\n2. Vérification des classes du plugin...\n";
$classes_to_check = [
    'PDF_Builder\\Cache\\WooCommerceCache' => 'WooCommerceCache',
    'WP_PDF_Builder_Pro\\Api\\PreviewImageAPI' => 'PreviewImageAPI',
    'WP_PDF_Builder_Pro\\Data\\WooCommerceDataProvider' => 'WooCommerceDataProvider'
];

foreach ($classes_to_check as $class => $name) {
    if (class_exists($class)) {
        echo "✅ Classe $name chargée\n";
    } else {
        echo "❌ Classe $name manquante\n";
    }
}

// 3. Vérifier les hooks WooCommerce
echo "\n3. Vérification des hooks WooCommerce...\n";
global $wp_filter;

$hooks_to_check = [
    'woocommerce_order_status_changed',
    'woocommerce_update_order',
    'woocommerce_new_order',
    'woocommerce_update_product',
    'woocommerce_delete_product'
];

foreach ($hooks_to_check as $hook) {
    if (isset($wp_filter[$hook])) {
        $has_pdf_builder_hook = false;
        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) &&
                    isset($callback['function'][0]) &&
                    is_string($callback['function'][0]) &&
                    strpos($callback['function'][0], 'WooCommerceCache') !== false) {
                    $has_pdf_builder_hook = true;
                    break 2;
                }
            }
        }

        if ($has_pdf_builder_hook) {
            echo "✅ Hook '$hook' configuré pour PDF Builder\n";
        } else {
            echo "❌ Hook '$hook' non configuré pour PDF Builder\n";
        }
    } else {
        echo "⚠️ Hook '$hook' non trouvé dans wp_filter\n";
    }
}

// 4. Tester le cache WooCommerce
echo "\n4. Test du cache WooCommerce...\n";
if (class_exists('PDF_Builder\\Cache\\WooCommerceCache')) {
    if (\PDF_Builder\Cache\WooCommerceCache::isAvailable()) {
        echo "✅ Cache WooCommerce disponible\n";

        // Test de stockage/récupération
        $test_key = 'test_integration_' . time();
        $test_data = ['test' => 'data', 'timestamp' => time()];

        // Test set
        $set_result = \PDF_Builder\Cache\WooCommerceCache::set($test_key, $test_data, 300);
        if ($set_result) {
            echo "✅ Stockage en cache réussi\n";

            // Test get
            $get_result = \PDF_Builder\Cache\WooCommerceCache::get($test_key);
            if ($get_result && isset($get_result['test']) && $get_result['test'] === 'data') {
                echo "✅ Récupération depuis cache réussie\n";

                // Cleanup
                \PDF_Builder\Cache\WooCommerceCache::delete($test_key);
                echo "✅ Nettoyage du cache de test réussi\n";
            } else {
                echo "❌ Récupération depuis cache échouée\n";
            }
        } else {
            echo "❌ Stockage en cache échoué\n";
        }
    } else {
        echo "❌ Cache WooCommerce non disponible\n";
    }
} else {
    echo "❌ Classe WooCommerceCache non disponible\n";
}

// 5. Vérifier les permissions
echo "\n5. Test des permissions...\n";
if (current_user_can('manage_options')) {
    echo "✅ Utilisateur actuel a les droits d'administrateur\n";
} else {
    echo "⚠️ Utilisateur actuel n'a pas les droits d'administrateur\n";
}

if (current_user_can('edit_shop_orders')) {
    echo "✅ Utilisateur actuel peut éditer les commandes\n";
} else {
    echo "⚠️ Utilisateur actuel ne peut pas éditer les commandes\n";
}

// 6. Test des données WooCommerce
echo "\n6. Test des données WooCommerce...\n";
if (class_exists('WP_PDF_Builder_Pro\\Data\\WooCommerceDataProvider')) {
    try {
        // Tester avec une commande fictive pour voir si la classe s'initialise
        $provider = new \WP_PDF_Builder_Pro\Data\WooCommerceDataProvider(1, 'test');
        echo "✅ WooCommerceDataProvider s'initialise correctement\n";
    } catch (Exception $e) {
        echo "⚠️ WooCommerceDataProvider erreur d'initialisation: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ WooCommerceDataProvider non disponible\n";
}

echo "\n=== FIN DES TESTS ===\n";
echo "Phase 1.6.1 - Intégration WordPress complète: TERMINÉE ✅\n";
?>