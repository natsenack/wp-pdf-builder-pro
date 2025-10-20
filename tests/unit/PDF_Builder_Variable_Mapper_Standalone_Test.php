<?php
/**
 * Tests unitaires simplifiés pour PDF_Builder_Variable_Mapper
 * Version standalone sans dépendances WordPress
 */

// Définir les constantes nécessaires
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

// Mock des fonctions WordPress nécessaires
if (!function_exists('get_option')) {
    function get_option($option, $default = '') {
        $options = [
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'timezone_string' => 'Europe/Paris'
        ];
        return $options[$option] ?? $default;
    }
}

if (!function_exists('wp_date')) {
    function wp_date($format, $timestamp = null) {
        return date($format, $timestamp ?: time());
    }
}

if (!function_exists('wc_price')) {
    function wc_price($price, $args = []) {
        return number_format($price, 2, ',', ' ') . ' €';
    }
}

if (!function_exists('date_i18n')) {
    function date_i18n($format, $timestamp = null) {
        return date($format, $timestamp ?: time());
    }
}

if (!function_exists('get_woocommerce_currency')) {
    function get_woocommerce_currency() {
        return 'EUR';
    }
}

if (!function_exists('wc_get_order_statuses')) {
    function wc_get_order_statuses() {
        // Statuts WooCommerce par défaut
        $default_statuses = [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'on-hold' => 'En attente',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
            'failed' => 'Échouée'
        ];

        // Ajouter des statuts personnalisés possibles (ex: plugins comme wc-devis)
        $custom_statuses = [
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

        // Fusionner et retourner tous les statuts
        return array_merge($default_statuses, $custom_statuses);
    }
}

if (!function_exists('WC')) {
    function WC() {
        static $wc = null;
        if ($wc === null) {
            $wc = new stdClass();
            $wc->countries = new class {
                public function get_countries() {
                    return [
                        'FR' => 'France',
                        'US' => 'United States',
                        'GB' => 'United Kingdom',
                        'DE' => 'Germany',
                        'ES' => 'Spain'
                    ];
                }
            };
        }
        return $wc;
    }
}

// Mock d'une commande WooCommerce simplifiée
class MockWCOrder {
    public function get_id() { return 123; }
    public function get_order_number() { return '#123'; }
    public function get_date_created() { return new DateTime('2025-10-20 10:30:00'); }
    public function get_date_modified() { return new DateTime('2025-10-20 11:00:00'); }
    public function get_status() { return 'completed'; }
    public function get_currency() { return 'EUR'; }
    public function get_total() { return '150.00'; }
    public function get_subtotal() { return '120.00'; }
    public function get_total_tax() { return '30.00'; }
    public function get_shipping_total() { return '10.00'; }
    public function get_discount_total() { return '0.00'; }
    public function get_payment_method_title() { return 'Carte bancaire'; }
    public function get_payment_method() { return 'stripe'; }
    public function get_transaction_id() { return 'txn_123456'; }
    public function get_customer_note() { return 'Test order'; }
    public function get_order_key() { return 'wc_order_test_key'; }
    public function get_customer_id() { return 1; }
    public function get_billing_email() { return 'test@example.com'; }
    public function get_billing_first_name() { return 'John'; }
    public function get_billing_last_name() { return 'Doe'; }
    public function get_formatted_billing_full_name() { return 'John Doe'; }
    public function get_billing_company() { return 'Test Company'; }
    public function get_billing_address_1() { return '123 Test Street'; }
    public function get_billing_address_2() { return 'Apt 4B'; }
    public function get_billing_city() { return 'Test City'; }
    public function get_billing_state() { return 'Test State'; }
    public function get_billing_postcode() { return '12345'; }
    public function get_billing_country() { return 'FR'; }
    public function get_formatted_billing_address() { return "123 Test Street\nApt 4B\nTest City 12345\nFrance"; }
    public function get_billing_phone() { return '+33123456789'; }
    public function get_shipping_first_name() { return 'John'; }
    public function get_shipping_last_name() { return 'Doe'; }
    public function get_shipping_company() { return 'Test Company'; }
    public function get_shipping_address_1() { return '123 Test Street'; }
    public function get_shipping_address_2() { return 'Apt 4B'; }
    public function get_shipping_city() { return 'Test City'; }
    public function get_shipping_state() { return 'Test State'; }
    public function get_shipping_postcode() { return '12345'; }
    public function get_shipping_country() { return 'FR'; }
    public function get_formatted_shipping_address() { return "123 Test Street\nApt 4B\nTest City 12345\nFrance"; }
    public function get_items() {
        return [
            new MockOrderItem()
        ];
    }

    public function get_fees() {
        return [
            new MockOrderFee()
        ];
    }
}

class MockOrderItem {
    public function get_name() { return 'Test Product'; }
    public function get_quantity() { return 2; }
    public function get_total() { return '100.00'; }
    public function get_product() {
        return new MockProduct();
    }
}

class MockProduct {
    public function get_price() { return '50.00'; }
    public function get_sku() { return 'TEST-SKU'; }
}

class MockOrderFee {
    public function get_name() { return 'Frais de port'; }
    public function get_total() { return '10.00'; }
}

// Inclure la classe VariableMapper directement
require_once __DIR__ . '/../../src/Managers/PDF_Builder_Variable_Mapper.php';

class PDF_Builder_Variable_Mapper_Standalone_Test {

    private $mapper;
    private $mockOrder;

    public function __construct() {
        // Créer une commande mock
        $this->mockOrder = new MockWCOrder();

        // Initialiser le mapper
        $this->mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper($this->mockOrder);
    }

    private function assert($condition, $message = '') {
        if (!$condition) {
            echo "❌ ÉCHEC: $message\n";
            return false;
        }
        echo "✅ PASS: $message\n";
        return true;
    }

    public function test_can_instantiate_mapper() {
        return $this->assert(
            $this->mapper instanceof \PDF_Builder\Managers\PDFBuilderVariableMapper,
            "Le mapper devrait être une instance de PDFBuilderVariableMapper"
        );
    }

    public function test_getAllVariables_returns_array() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            is_array($variables),
            "getAllVariables devrait retourner un tableau"
        );

        $success &= $this->assert(
            !empty($variables),
            "Le tableau de variables ne devrait pas être vide"
        );

        return $success;
    }

    public function test_order_variables() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists('order_number', $variables),
            "La variable order_number devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('order_total', $variables),
            "La variable order_total devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('order_status', $variables),
            "La variable order_status devrait exister"
        );

        $success &= $this->assert(
            $variables['order_number'] === '#123',
            "order_number devrait être '#123'"
        );

        $success &= $this->assert(
            $variables['order_status'] === 'Terminée',
            "order_status devrait être 'Terminée'"
        );

        return $success;
    }

    public function test_customer_variables() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists('customer_name', $variables),
            "La variable customer_name devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('customer_email', $variables),
            "La variable customer_email devrait exister"
        );

        $success &= $this->assert(
            $variables['customer_name'] === 'John Doe',
            "customer_name devrait être 'John Doe'"
        );

        return $success;
    }

    public function test_null_order_handling() {
        $mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper(null);
        $variables = $mapper->getAllVariables();

        $success = $this->assert(
            is_array($variables),
            "getAllVariables avec commande null devrait retourner un tableau"
        );

        return $success;
    }

    public function test_product_variables_includes_fees() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists('products_list', $variables),
            "La variable products_list devrait exister"
        );

        $success &= $this->assert(
            strpos($variables['products_list'], 'Test Product') !== false,
            "La liste devrait contenir le produit"
        );

        $success &= $this->assert(
            strpos($variables['products_list'], 'Frais de port') !== false,
            "La liste devrait contenir les frais de port"
        );

        return $success;
    }

    public function run() {
        echo "\n🧪 TESTS PDF_Builder_Variable_Mapper (Standalone)\n";
        echo "================================================\n\n";

        $tests = [
            'test_can_instantiate_mapper',
            'test_getAllVariables_returns_array',
            'test_order_variables',
            'test_customer_variables',
            'test_product_variables_includes_fees',
            'test_null_order_handling'
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test) {
            echo "Exécution de $test...\n";
            try {
                if ($this->$test()) {
                    $passed++;
                }
            } catch (Exception $e) {
                echo "❌ ERREUR dans $test: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }

        echo "================================================\n";
        echo "RÉSULTATS: $passed/$total tests réussis\n";

        if ($passed === $total) {
            echo "🎉 TOUS LES TESTS RÉUSSIS !\n";
            return true;
        } else {
            echo "⚠️  Quelques tests ont échoué\n";
            return false;
        }
    }
}

// Exécuter les tests
$test = new PDF_Builder_Variable_Mapper_Standalone_Test();
$test->run();