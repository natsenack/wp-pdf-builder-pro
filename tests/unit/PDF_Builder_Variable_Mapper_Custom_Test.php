<?php
/**
 * Tests unitaires simples pour PDF_Builder_Variable_Mapper
 * Sans dépendance PHPUnit pour compatibilité
 */

class PDF_Builder_Variable_Mapper_Test {

    private $mapper;
    private $mockOrder;

    public function __construct() {
        // Créer une commande mock pour les tests
        $this->mockOrder = wc_get_order(123);

        // Initialiser le mapper avec la commande mock
        $this->mapper = new PDF_Builder_Variable_Mapper($this->mockOrder);
    }

    /**
     * Fonction d'assertion simple
     */
    private function assert($condition, $message = '') {
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
            $this->mapper instanceof PDF_Builder_Variable_Mapper,
            "Le mapper devrait être une instance de PDF_Builder_Variable_Mapper"
        );
    }

    /**
     * Test récupération de toutes les variables
     */
    public function test_get_all_variables_returns_array() {
        $variables = $this->mapper->get_all_variables();

        $success = $this->assert(
            is_array($variables),
            "get_all_variables devrait retourner un tableau"
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
    public function test_order_variables() {
        $variables = $this->mapper->get_all_variables();

        $success = $this->assert(
            array_key_exists('order_number', $variables),
            "La variable order_number devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('order_date', $variables),
            "La variable order_date devrait exister"
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
            "order_number devrait être '#123', obtenu: " . $variables['order_number']
        );

        $success &= $this->assert(
            $variables['order_status'] === 'completed',
            "order_status devrait être 'completed', obtenu: " . $variables['order_status']
        );

        $success &= $this->assert(
            $variables['order_total'] === '150.00',
            "order_total devrait être '150.00', obtenu: " . $variables['order_total']
        );

        return $success;
    }

    /**
     * Test variables client
     */
    public function test_customer_variables() {
        $variables = $this->mapper->get_all_variables();

        $success = $this->assert(
            array_key_exists('customer_name', $variables),
            "La variable customer_name devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('customer_email', $variables),
            "La variable customer_email devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('customer_phone', $variables),
            "La variable customer_phone devrait exister"
        );

        $success &= $this->assert(
            $variables['customer_name'] === 'John Doe',
            "customer_name devrait être 'John Doe', obtenu: " . $variables['customer_name']
        );

        $success &= $this->assert(
            $variables['customer_email'] === 'test@example.com',
            "customer_email devrait être 'test@example.com', obtenu: " . $variables['customer_email']
        );

        return $success;
    }

    /**
     * Test variables d'adresse
     */
    public function test_address_variables() {
        $variables = $this->mapper->get_all_variables();

        $success = $this->assert(
            array_key_exists('billing_address', $variables),
            "La variable billing_address devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('shipping_address', $variables),
            "La variable shipping_address devrait exister"
        );

        $success &= $this->assert(
            strpos($variables['billing_address'], '123 Test Street') !== false,
            "billing_address devrait contenir '123 Test Street'"
        );

        $success &= $this->assert(
            strpos($variables['billing_address'], 'Test City') !== false,
            "billing_address devrait contenir 'Test City'"
        );

        return $success;
    }

    /**
     * Test variables financières
     */
    public function test_financial_variables() {
        $variables = $this->mapper->get_all_variables();

        $success = $this->assert(
            array_key_exists('subtotal', $variables),
            "La variable subtotal devrait exister"
        );

        $success &= $this->assert(
            array_key_exists('tax_amount', $variables),
            "La variable tax_amount devrait exister"
        );

        $success &= $this->assert(
            $variables['subtotal'] === '130.00',
            "subtotal devrait être '130.00' (avec frais inclus), obtenu: " . $variables['subtotal']
        );

        $success &= $this->assert(
            $variables['tax_amount'] === '30.00',
            "tax_amount devrait être '30.00', obtenu: " . $variables['tax_amount']
        );

        return $success;
    }

    /**
     * Test gestion des commandes nulles
     */
    public function test_null_order_handling() {
        $mapper = new PDF_Builder_Variable_Mapper(null);
        $variables = $mapper->get_all_variables();

        $success = $this->assert(
            is_array($variables),
            "get_all_variables avec commande null devrait retourner un tableau"
        );

        $success &= $this->assert(
            array_key_exists('order_number', $variables),
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
            'test_can_instantiate_mapper',
            'test_get_all_variables_returns_array',
            'test_order_variables',
            'test_customer_variables',
            'test_address_variables',
            'test_financial_variables',
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

// Exécuter les tests si le fichier est appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    $test = new PDF_Builder_Variable_Mapper_Test();
    $test->run();
}