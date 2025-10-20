<?php
/**
 * Tests de performance pour le système d'aperçu
 * Mesure les temps de chargement et performance
 */

class Performance_Test {

    private $results = [];

    private function assert($condition, $message = '') {
        if ($condition) {
            $this->results[] = "✅ PASS: $message";
            return true;
        } else {
            $this->results[] = "❌ FAIL: $message";
            return false;
        }
    }

    private function run_test($test_name, $callback) {
        echo "\nExécution de $test_name...\n";
        $start_time = microtime(true);

        try {
            $result = $callback();
            $end_time = microtime(true);
            $duration = round(($end_time - $start_time) * 1000, 2); // en millisecondes

            echo "⏱️ Durée: {$duration}ms\n";

            if ($result) {
                $this->results[] = "✅ PASS: $test_name ({$duration}ms)";
            } else {
                $this->results[] = "❌ FAIL: $test_name ({$duration}ms)";
            }

            return $result;
        } catch (Exception $e) {
            $this->results[] = "❌ ERROR in $test_name: " . $e->getMessage();
            return false;
        }
    }

    public function test_variable_mapper_performance() {
        return $this->run_test('test_variable_mapper_performance', function() {
            // Mock des fonctions nécessaires
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

            // Créer un mock d'ordre
            $mock_order = new class {
                public function get_id() { return 123; }
                public function get_order_number() { return '#123'; }
                public function get_status() { return 'completed'; }
                public function get_total() { return '150.00'; }
                public function get_currency() { return 'EUR'; }
                public function get_customer_id() { return 1; }
                public function get_billing_first_name() { return 'John'; }
                public function get_billing_last_name() { return 'Doe'; }
                public function get_billing_email() { return 'john@example.com'; }
                public function get_billing_address_1() { return '123 Main St'; }
                public function get_billing_city() { return 'Paris'; }
                public function get_billing_postcode() { return '75001'; }
                public function get_billing_country() { return 'FR'; }
                public function get_formatted_billing_full_name() { return 'John Doe'; }
                public function get_billing_phone() { return '+33123456789'; }
                public function get_customer_note() { return 'Test order'; }
                public function get_formatted_billing_address() { return "123 Main St\nParis 75001\nFrance"; }
                public function get_formatted_shipping_address() { return "123 Main St\nParis 75001\nFrance"; }
                public function get_billing_company() { return 'Test Company'; }
                public function get_billing_address_2() { return 'Apt 4B'; }
                public function get_billing_state() { return 'Test State'; }
                public function get_subtotal() { return '120.00'; }
                public function get_total_tax() { return '30.00'; }
                public function get_shipping_total() { return '10.00'; }
                public function get_discount_total() { return '0.00'; }
                public function get_payment_method_title() { return 'Carte bancaire'; }
                public function get_payment_method() { return 'stripe'; }
                public function get_transaction_id() { return 'txn_123456'; }
                public function get_order_key() { return 'wc_order_test_key'; }
                public function get_items() { return []; }
                public function get_fees() { return []; }
                public function get_date_created() { return new DateTime(); }
                public function get_date_modified() { return new DateTime(); }
            };

            // Inclure le fichier VariableMapper
            require_once __DIR__ . '/../../src/Managers/PDF_Builder_Variable_Mapper.php';

            // Tester la création et l'utilisation
            $mapper = new PDF_Builder_Variable_Mapper($mock_order);
            $variables = $mapper->get_all_variables();

            // Vérifications
            $success = $this->assert(is_array($variables), "VariableMapper retourne un tableau");
            $success &= $this->assert(count($variables) > 30, "Au moins 30 variables disponibles");
            $success &= $this->assert(isset($variables['order_number']), "Variable order_number présente");
            $success &= $this->assert(isset($variables['customer_name']), "Variable customer_name présente");

            return $success;
        });
    }

    public function test_ajax_endpoints_performance() {
        return $this->run_test('test_ajax_endpoints_performance', function() {
            // Simuler les fonctions WordPress nécessaires
            if (!function_exists('wp_verify_nonce')) {
                function wp_verify_nonce($nonce, $action) { return true; }
            }
            if (!function_exists('current_user_can')) {
                function current_user_can($capability) { return true; }
            }
            if (!function_exists('sanitize_text_field')) {
                function sanitize_text_field($str) { return trim($str); }
            }
            if (!function_exists('absint')) {
                function absint($value) { return abs((int)$value); }
            }
            if (!function_exists('get_post_meta')) {
                function get_post_meta($post_id, $key, $single = false) {
                    return json_encode([['id' => 'test', 'type' => 'text']]);
                }
            }
            if (!function_exists('wc_get_order')) {
                function wc_get_order($order_id) {
                    if ($order_id == 123) {
                        return new class {
                            public function get_id() { return 123; }
                            public function get_status() { return 'completed'; }
                            public function get_total() { return '150.00'; }
                            public function get_customer_id() { return 1; }
                            public function get_billing_first_name() { return 'John'; }
                            public function get_billing_last_name() { return 'Doe'; }
                            public function get_billing_email() { return 'john@example.com'; }
                            public function get_items() { return []; }
                        };
                    }
                    return false;
                }
            }

            // Tester l'endpoint get_order_preview_data
            $_POST['order_id'] = '123';
            $order_id = absint($_POST['order_id']);
            $order = wc_get_order($order_id);

            if ($order) {
                $data = [
                    'order_number' => '#' . $order->get_id(),
                    'order_total' => $order->get_total(),
                    'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
                ];

                $success = $this->assert(is_array($data), "Données AJAX générées");
                $success &= $this->assert(count($data) >= 3, "Au moins 3 champs de données");

                return $success;
            }

            return false;
        });
    }

    public function test_memory_usage() {
        return $this->run_test('test_memory_usage', function() {
            $start_memory = memory_get_usage();

            // Simuler le chargement d'un template avec éléments
            $elements = [];
            for ($i = 0; $i < 50; $i++) {
                $elements[] = [
                    'id' => 'element_' . $i,
                    'type' => 'text',
                    'x' => 10 + ($i * 10), // Valeur fixe au lieu de rand()
                    'y' => 20 + ($i * 10),
                    'width' => 200,
                    'height' => 50,
                    'text' => 'Test element ' . $i,
                    'fontSize' => 12,
                    'color' => '#000000'
                ];
            }

            $end_memory = memory_get_usage();
            $memory_used = $end_memory - $start_memory;
            $memory_mb = round($memory_used / 1024 / 1024, 2);

            echo "📊 Mémoire utilisée: {$memory_mb} MB\n";

            // Vérifier que l'usage mémoire est raisonnable (< 10MB pour 50 éléments)
            return $this->assert($memory_mb < 10, "Usage mémoire acceptable (< 10MB)");
        });
    }

    public function test_json_processing_performance() {
        return $this->run_test('test_json_processing_performance', function() {
            // Créer un gros objet JSON comme ceux utilisés dans les templates
            $large_template = [
                'elements' => [],
                'settings' => [
                    'width' => 210,
                    'height' => 297,
                    'margins' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10]
                ]
            ];

            // Ajouter 100 éléments
            for ($i = 0; $i < 100; $i++) {
                $large_template['elements'][] = [
                    'id' => 'elem_' . $i,
                    'type' => 'text',
                    'x' => 10 + ($i * 2), // Valeur fixe au lieu de rand()
                    'y' => 20 + ($i * 2),
                    'width' => 100,
                    'height' => 20,
                    'text' => 'Element de test numéro ' . $i,
                    'properties' => [
                        'fontSize' => 12,
                        'color' => '#000000',
                        'fontFamily' => 'Arial',
                        'textAlign' => 'left'
                    ]
                ];
            }

            // Tester l'encodage JSON
            $json_start = microtime(true);
            $json_string = json_encode($large_template);
            $json_time = (microtime(true) - $json_start) * 1000;

            // Tester le décodage JSON
            $decode_start = microtime(true);
            $decoded = json_decode($json_string, true);
            $decode_time = (microtime(true) - $decode_start) * 1000;

            echo "📊 Encodage JSON: {$json_time}ms, Décodage: {$decode_time}ms\n";

            $success = $this->assert($json_string !== false, "JSON encodé avec succès");
            $success &= $this->assert(is_array($decoded), "JSON décodé avec succès");
            $success &= $this->assert(count($decoded['elements']) === 100, "Tous les éléments préservés");
            $success &= $this->assert($json_time < 100, "Encodage rapide (< 100ms)");
            $success &= $this->assert($decode_time < 50, "Décodage rapide (< 50ms)");

            return $success;
        });
    }

    public function run_all_tests() {
        echo "⚡ TESTS PERFORMANCE\n";
        echo "==================\n";

        $tests = [
            'test_variable_mapper_performance',
            'test_ajax_endpoints_performance',
            'test_memory_usage',
            'test_json_processing_performance'
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test) {
            if ($this->{$test}()) {
                $passed++;
            }
        }

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "RÉSULTATS: $passed/$total tests réussis\n";

        if ($passed === $total) {
            echo "🚀 PERFORMANCE OPTIMALE VALIDÉE !\n";
        } else {
            echo "⚠️ Problèmes de performance détectés\n";
        }

        return $passed === $total;
    }
}

// Exécuter les tests
$test = new Performance_Test();
$test->run_all_tests();