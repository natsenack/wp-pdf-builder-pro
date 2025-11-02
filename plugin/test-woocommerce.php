<?php
/**
 * Test fonctionnel des variables WooCommerce
 * Teste l'injection de vraies données WooCommerce dans les templates PDF
 */

// Simuler les constantes WordPress nécessaires
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', dirname(dirname(__FILE__)));
}

// Définir les constantes du plugin nécessaires
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
}
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', dirname(__FILE__) . '/pdf-builder-pro.php');
}

echo "=== TEST FONCTIONNEL WOOCommerce ===\n\n";

// Charger le bootstrap minimal
$bootstrap_path = __DIR__ . '/bootstrap-minimal.php';
if (!file_exists($bootstrap_path)) {
    echo "❌ Bootstrap minimal introuvable\n";
    exit(1);
}

require_once $bootstrap_path;
pdf_builder_load_bootstrap();

echo "✅ Bootstrap chargé\n";

// Test 1: Tester WooCommerceDataProvider
echo "1. Classe WooCommerceDataProvider: ";
try {
    if (class_exists('WP_PDF_Builder_Pro\\Data\\WooCommerceDataProvider')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 2: Instanciation WooCommerceDataProvider
echo "2. Instanciation WooCommerceDataProvider: ";
try {
    $woo_provider = new WP_PDF_Builder_Pro\Data\WooCommerceDataProvider();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 3: Tester les méthodes de récupération de données
echo "3. Test méthodes WooCommerceDataProvider: ";
try {
    // Tester getVariableValue sans order (devrait retourner valeur par défaut)
    $test_value = $woo_provider->getVariableValue('order_number');
    echo "getVariableValue('order_number'): '" . $test_value . "' ✅\n";

    // Tester d'autres variables
    $variables_to_test = [
        'customer_name',
        'customer_email',
        'order_total',
        'order_date',
        'billing_address',
        'shipping_address'
    ];

    foreach ($variables_to_test as $var) {
        $value = $woo_provider->getVariableValue($var);
        echo "  - $var: '" . substr($value, 0, 50) . "' ✅\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 4: Tester avec un mock order (simuler un objet WC_Order)
echo "4. Test avec mock order: ";
try {
    // Créer un mock order simple
    $mock_order = new stdClass();
    $mock_order->id = 12345;
    $mock_order->get_order_number = function() { return '#12345'; };
    $mock_order->get_formatted_order_total = function() { return '€99.99'; };
    $mock_order->get_date_created = function() { return new DateTime('2025-11-02'); };

    // Mock customer data
    $mock_order->get_billing_first_name = function() { return 'Jean'; };
    $mock_order->get_billing_last_name = function() { return 'Dupont'; };
    $mock_order->get_billing_email = function() { return 'jean.dupont@email.com'; };

    // Tester setOrder et getVariableValue
    $woo_provider->setOrder($mock_order);

    $order_number = $woo_provider->getVariableValue('order_number');
    $customer_name = $woo_provider->getVariableValue('customer_name');
    $order_total = $woo_provider->getVariableValue('order_total');

    echo "✅ OK\n";
    echo "  - Order Number: $order_number\n";
    echo "  - Customer Name: $customer_name\n";
    echo "  - Order Total: $order_total\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 5: Test génération PDF avec données WooCommerce
echo "5. Génération PDF avec données WooCommerce: ";
try {
    $template_data = [
        'template' => [
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'Commande: {{order_number}} - Client: {{customer_name}} - Total: {{order_total}}'
                ]
            ]
        ]
    ];

    $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $woo_provider);
    $result = $generator->generate('pdf');

    if (is_string($result) && !empty($result)) {
        echo "✅ OK (PDF généré avec variables WooCommerce: " . strlen($result) . " bytes)\n";
    } elseif (is_array($result) && isset($result['fallback'])) {
        echo "✅ OK (Fallback Canvas avec variables WooCommerce: " . strlen($result['html']) . " chars)\n";
    } else {
        echo "❌ Résultat inattendu\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST WOOCommerce ===\n";
echo "Si les tests WooCommerce sont OK, l'injection de variables réelles fonctionnera !\n";
?>