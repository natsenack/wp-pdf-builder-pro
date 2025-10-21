<?php
/**
 * Tests unitaires simples pour PDF_Builder_Variable_Mapper
 * Sans dépendance PHPUnit pour compatibilité
 */

// Définir les constantes nécessaires pour l autoloader
if (!defined("PHPUNIT_RUNNING")) {
    define("PHPUNIT_RUNNING", true);
}
if (!defined("ABSPATH")) {
    define("ABSPATH", __DIR__ . "/../../");
}

// Fonction helper pour les tests (remplace trailingslashit de WordPress)
if (!function_exists("trailingslashit")) {
    function trailingslashit($string) {
        return rtrim($string, "/\\") . "/";
    }
}

// Fonctions WordPress nécessaires pour les tests
if (!function_exists("date_i18n")) {
    function date_i18n($format, $timestamp = null) {
        return date($format, $timestamp ?: time());
    }
}

if (!function_exists("wp_date")) {
    function wp_date($format, $timestamp = null) {
        return date($format, $timestamp ?: time());
    }
}

if (!function_exists("get_option")) {
    function get_option($option, $default = null) {
        // Valeurs par défaut pour les tests
        $defaults = [
            "date_format" => "d/m/Y",
            "time_format" => "H:i:s"
        ];
        return $defaults[$option] ?? $default;
    }
}

// Fonctions WooCommerce nécessaires pour les tests
if (!function_exists("wc_price")) {
    function wc_price($price, $args = []) {
        $price = floatval($price); // Convertir en float
        $defaults = [
            "currency" => "EUR",
            "decimal_separator" => ",",
            "thousand_separator" => " ",
            "decimals" => 2
        ];
        $args = array_merge($defaults, $args);
        return number_format($price, $args["decimals"], $args["decimal_separator"], $args["thousand_separator"]) . " " . $args["currency"];
    }
}

if (!function_exists("wc_get_order_statuses")) {
    function wc_get_order_statuses() {
        return [
            "pending" => "En attente",
            "processing" => "En cours",
            "on-hold" => "En attente",
            "completed" => "Terminée",
            "cancelled" => "Annulée",
            "refunded" => "Remboursée",
            "failed" => "Échouée"
        ];
    }
}

// Charger l autoloader
require_once __DIR__ . "/../../core/autoloader.php";

// Initialiser l autoloader manuellement pour les tests
PDF_Builder_Autoloader::init(__DIR__ . "/../../");

// Inclure directement la classe pour les tests
require_once __DIR__ . "/../../src/Managers/PDF_Builder_Variable_Mapper.php";

class PDFBuilderVariableMapperTest {

    private $mapper;
    private $mockOrder;

    public function __construct() {
        try {
            // Créer une commande mock pour les tests
            $this->mockOrder = $this->createMockOrder();

            // Initialiser le mapper avec la commande mock
            $this->mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper($this->mockOrder);
            echo "✅ Mapper initialisé avec succès\n";
        } catch (Exception $e) {
            echo "❌ Erreur lors de l initialisation: " . $e->getMessage() . "\n";
            $this->mapper = null;
        }
    }

    /**
     * Créer un mock d ordre WooCommerce pour les tests
     */
    private function createMockOrder() {
        return new class {
            private $data = [
                "id" => 123,
                "order_number" => "#123",
                "status" => "completed",
                "total" => "150.00",
                "currency" => "EUR",
                "date_created" => null,
                "date_modified" => null,
                "customer_id" => 1,
                "billing" => [
                    "first_name" => "John",
                    "last_name" => "Doe",
                    "email" => "test@example.com",
                    "phone" => "+33123456789",
                    "address_1" => "123 Test Street",
                    "address_2" => "Apt 4B",
                    "city" => "Test City",
                    "state" => "Test State",
                    "postcode" => "12345",
                    "country" => "FR"
                ],
                "shipping" => [
                    "first_name" => "John",
                    "last_name" => "Doe",
                    "address_1" => "123 Test Street",
                    "address_2" => "Apt 4B",
                    "city" => "Test City",
                    "state" => "Test State",
                    "postcode" => "12345",
                    "country" => "FR"
                ]
            ];

            public function get_id() { return $this->data["id"]; }
            public function get_order_number() { return $this->data["order_number"]; }
            public function get_status() { return $this->data["status"]; }
            public function get_total() { return $this->data["total"]; }
            public function get_currency() { return $this->data["currency"]; }
            public function get_date_created() { return $this->data["date_created"]; }
            public function get_date_modified() { return $this->data["date_modified"]; }
            public function get_customer_id() { return $this->data["customer_id"]; }
            public function get_billing_first_name() { return $this->data["billing"]["first_name"]; }
            public function get_billing_last_name() { return $this->data["billing"]["last_name"]; }
            public function get_billing_email() { return $this->data["billing"]["email"]; }
            public function get_billing_phone() { return $this->data["billing"]["phone"]; }
            public function get_billing_address_1() { return $this->data["billing"]["address_1"]; }
            public function get_billing_address_2() { return $this->data["billing"]["address_2"]; }
            public function get_billing_city() { return $this->data["billing"]["city"]; }
            public function get_billing_state() { return $this->data["billing"]["state"]; }
            public function get_billing_postcode() { return $this->data["billing"]["postcode"]; }
            public function get_billing_country() { return $this->data["billing"]["country"]; }
            public function get_shipping_first_name() { return $this->data["shipping"]["first_name"]; }
            public function get_shipping_last_name() { return $this->data["shipping"]["last_name"]; }
            public function get_shipping_address_1() { return $this->data["shipping"]["address_1"]; }
            public function get_shipping_address_2() { return $this->data["shipping"]["address_2"]; }
            public function get_shipping_city() { return $this->data["shipping"]["city"]; }
            public function get_shipping_state() { return $this->data["shipping"]["state"]; }
            public function get_shipping_postcode() { return $this->data["shipping"]["postcode"]; }
            public function get_shipping_country() { return $this->data["shipping"]["country"]; }
            public function get_subtotal() { return "130.00"; }
            public function get_total_tax() { return "30.00"; }
            public function get_shipping_total() { return "10.00"; }
            public function get_payment_method_title() { return "Carte de crédit"; }
            public function get_items() { return []; }
            public function get_fees() { return []; }
            public function get_formatted_billing_full_name() { return "John Doe"; }
            public function get_formatted_shipping_full_name() { return "John Doe"; }
            public function get_formatted_billing_address() { return "John Doe\n123 Test Street\nApt 4B\nTest City, Test State 12345\nFrance"; }
            public function get_formatted_shipping_address() { return "John Doe\n123 Test Street\nApt 4B\nTest City, Test State 12345\nFrance"; }
            public function get_customer_note() { return "Test customer note"; }
            public function __call($method, $args) {
                // Retourner des valeurs appropriées selon le type de méthode
                if (strpos($method, "get_") === 0) {
                    if (strpos($method, "_items") !== false || strpos($method, "_products") !== false || strpos($method, "_get_items") !== false) {
                        return []; // Retourner un array vide pour les méthodes d items
                    }
                    if (strpos($method, "_variables") !== false || strpos($method, "_data") !== false) {
                        return []; // Retourner un array vide pour les méthodes de données
                    }
                    return "mock_value"; // Retourner une string pour les autres getters
                }
                return null;
            }
        };
    }

    private function assert($condition, $message = "") {
        if (!$condition) {
            echo "❌ ÉCHEC: $message\n";
            return false;
        }
        echo "✅ PASS: $message\n";
        return true;
    }

    /**
     * Test que le mapper peut être instancié
     */
    public function test_can_instantiate_mapper() {
        return $this->assert(
            $this->mapper instanceof \PDF_Builder\Managers\PDFBuilderVariableMapper,
            "Le mapper devrait être une instance de PDFBuilderVariableMapper"
        );
    }

    /**
     * Test récupération de toutes les variables
     */
    public function testGetAllVariablesReturnsArray() {
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

    /**
     * Test variables de commande
     */
    public function testOrderVariables() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists("order_number", $variables),
            "La variable order_number devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("order_date", $variables),
            "La variable order_date devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("order_total", $variables),
            "La variable order_total devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("order_status", $variables),
            "La variable order_status devrait exister"
        );

        $success &= $this->assert(
            $variables["order_number"] === "#123",
            "order_number devrait être \"#123\", obtenu: " . $variables["order_number"]
        );

        $success &= $this->assert(
            $variables["order_status"] === "Terminée",
            "order_status devrait être \"Terminée\", obtenu: " . $variables["order_status"]
        );

        $success &= $this->assert(
            $variables["order_total"] === "150,00 EUR",
            "order_total devrait être \"150,00 EUR\", obtenu: " . $variables["order_total"]
        );

        return $success;
    }

    /**
     * Test variables client
     */
    public function testCustomerVariables() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists("customer_name", $variables),
            "La variable customer_name devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("customer_email", $variables),
            "La variable customer_email devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("customer_phone", $variables),
            "La variable customer_phone devrait exister"
        );

        $success &= $this->assert(
            $variables["customer_name"] === "John Doe",
            "customer_name devrait être \"John Doe\", obtenu: " . $variables["customer_name"]
        );

        $success &= $this->assert(
            $variables["customer_email"] === "test@example.com",
            "customer_email devrait être \"test@example.com\", obtenu: " . $variables["customer_email"]
        );

        return $success;
    }

    /**
     * Test variables d adresse
     */
    public function testAddressVariables() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists("billing_address", $variables),
            "La variable billing_address devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("shipping_address", $variables),
            "La variable shipping_address devrait exister"
        );

        $success &= $this->assert(
            strpos($variables["billing_address"], "123 Test Street") !== false,
            "billing_address devrait contenir \"123 Test Street\""
        );

        $success &= $this->assert(
            strpos($variables["billing_address"], "Test City") !== false,
            "billing_address devrait contenir \"Test City\""
        );

        return $success;
    }

    /**
     * Test variables financières
     */
    public function testFinancialVariables() {
        $variables = $this->mapper->getAllVariables();

        $success = $this->assert(
            array_key_exists("subtotal", $variables),
            "La variable subtotal devrait exister"
        );

        $success &= $this->assert(
            array_key_exists("tax_amount", $variables),
            "La variable tax_amount devrait exister"
        );

        $success &= $this->assert(
            $variables["subtotal"] === "130,00 EUR",
            "subtotal devrait être \"130,00 EUR\" (avec frais inclus), obtenu: " . $variables["subtotal"]
        );

        $success &= $this->assert(
            $variables["tax_amount"] === "30,00 EUR",
            "tax_amount devrait être \"30,00 EUR\", obtenu: " . $variables["tax_amount"]
        );

        return $success;
    }

    /**
     * Test gestion des commandes nulles
     */
    public function test_null_order_handling() {
        $mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper(null);
        $variables = $mapper->getAllVariables();

        $success = $this->assert(
            is_array($variables),
            "getAllVariables avec commande null devrait retourner un tableau"
        );

        $success &= $this->assert(
            array_key_exists("order_number", $variables),
            "La variable order_number devrait exister même avec commande null"
        );

        return $success;
    }

    /**
     * Exécuter tous les tests
     */
    public function run() {
        echo "\n🧪 TESTS PDF_Builder_Variable_Mapper\n";
        echo "==================================\n\n";

        $tests = [
            "test_can_instantiate_mapper",
            "testGetAllVariablesReturnsArray",
            "testOrderVariables",
            "testCustomerVariables",
            "testAddressVariables",
            "testFinancialVariables",
            "test_null_order_handling"
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

        echo "==================================\n";
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

// Exécuter les tests automatiquement
$test = new PDFBuilderVariableMapperTest();
$result = $test->run();
exit($result ? 0 : 1);
