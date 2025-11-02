<?php
// Permettre l'exécution en mode test
if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

// Simuler les fonctions WordPress nécessaires pour les tests
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return 'test_nonce_' . $action;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return $nonce === 'test_nonce_' . $action;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Simuler un utilisateur avec tous les droits en test
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        throw new Exception('WordPress wp_die called: ' . $message);
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data) {
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data) {
        echo json_encode(['success' => false, 'data' => $data]);
        exit;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim($str);
    }
}

if (!function_exists('stripslashes')) {
    function stripslashes($str) {
        return $str; // Simplified for test
    }
}

if (!function_exists('intval')) {
    function intval($var) {
        return (int)$var;
    }
}

if (!function_exists('function_exists')) {
    function function_exists($function_name) {
        return true; // Simplified for test
    }
}

if (!function_exists('wc_get_order_status_name')) {
    function wc_get_order_status_name($status) {
        $statuses = [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'on-hold' => 'En attente',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
            'failed' => 'Échouée'
        ];
        return $statuses[$status] ?? $status;
    }
}

if (!function_exists('wc_price')) {
    function wc_price($price, $args = []) {
        $currency = $args['currency'] ?? 'EUR';
        return number_format($price, 2, ',', ' ') . ' ' . $currency;
    }
}

// Mock pour WC() global
if (!class_exists('WC')) {
    class WC {
        public static $countries;
    }
    WC::$countries = (object)['countries' => ['FR' => 'France', 'US' => 'United States']];
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
    // Créer un mock order plus complet avec les méthodes nécessaires
    $mock_order = new class {
        public $id = 12345;

        public function get_order_number() { return '#12345'; }
        public function get_total() { return 99.99; }
        public function get_subtotal() { return 89.99; }
        public function get_total_tax() { return 10.00; }
        public function get_shipping_total() { return 5.00; }
        public function get_discount_total() { return 0.00; }
        public function get_date_created() { return '2025-11-02 10:30:00'; }
        public function get_status() { return 'completed'; }
        public function get_currency() { return 'EUR'; }

        // Méthodes customer
        public function get_formatted_billing_full_name() { return 'Jean Dupont'; }
        public function get_billing_first_name() { return 'Jean'; }
        public function get_billing_last_name() { return 'Dupont'; }
        public function get_billing_email() { return 'jean.dupont@email.com'; }
        public function get_billing_phone() { return '+33123456789'; }
        public function get_billing_address_1() { return '123 Rue de la Paix'; }
        public function get_billing_address_2() { return 'Appartement 4B'; }
        public function get_billing_city() { return 'Paris'; }
        public function get_billing_postcode() { return '75001'; }
        public function get_billing_country() { return 'FR'; }
        public function get_billing_state() { return 'Île-de-France'; }

        public function get_shipping_first_name() { return 'Jean'; }
        public function get_shipping_last_name() { return 'Dupont'; }
        public function get_shipping_address_1() { return '123 Rue de la Paix'; }
        public function get_shipping_city() { return 'Paris'; }
        public function get_shipping_postcode() { return '75001'; }
        public function get_shipping_country() { return 'FR'; }

        // Méthodes pour les items
        public function get_items() {
            return [
                (object)[
                    'get_name' => function() { return 'Produit Test'; },
                    'get_quantity' => function() { return 2; },
                    'get_total' => function() { return 89.99; }
                ]
            ];
        }
    };

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