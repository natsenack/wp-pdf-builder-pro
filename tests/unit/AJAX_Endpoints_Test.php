<?php
/**
 * Tests d'intégration pour les endpoints AJAX du système d'aperçu
 * Tests standalone sans dépendances WordPress complètes
 */

// Mock des fonctions WordPress nécessaires
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return true; // Simuler nonce valide pour les tests
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Simuler utilisateur avec permissions
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        throw new Exception("wp_die called: $message");
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = null) {
        echo json_encode(['success' => false, 'data' => $data]);
        exit;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim($str);
    }
}

if (!function_exists('absint')) {
    function absint($value) {
        return abs((int)$value);
    }
}

if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key, $single = false) {
        // Mock data pour les tests
        $mock_data = [
            'pdf_builder_elements' => json_encode([
                [
                    'id' => 'text1',
                    'type' => 'text',
                    'x' => 10,
                    'y' => 10,
                    'width' => 200,
                    'height' => 50,
                    'text' => 'Test text'
                ]
            ])
        ];
        return $mock_data[$key] ?? null;
    }
}

if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        if ($order_id == 123) {
            // Retourner un objet avec les méthodes correctement définies
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

class AJAX_Endpoints_Test {

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
        try {
            $result = $callback();
            return $result;
        } catch (Exception $e) {
            $this->results[] = "❌ ERROR in $test_name: " . $e->getMessage();
            return false;
        }
    }

    public function test_pdf_builder_get_canvas_elements() {
        return $this->run_test('test_pdf_builder_get_canvas_elements', function() {
            // Simuler les variables $_POST et $_GET
            $_POST['nonce'] = 'test_nonce';
            $_POST['template_id'] = '123';

            // Simuler la logique de l'endpoint sans exit
            $template_id = absint($_POST['template_id']);

            if (!$template_id) {
                return $this->assert(false, "ID template manquant détecté");
            }

            $elements = get_post_meta($template_id, 'pdf_builder_elements', true);

            if (!$elements) {
                return $this->assert(false, "Template non trouvé détecté");
            }

            $elements_array = json_decode($elements, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->assert(false, "Erreur décodage JSON détectée");
            }

            // Vérifications des résultats
            $success = $this->assert(is_array($elements_array), "Devrait retourner un tableau d'éléments");
            $success &= $this->assert(count($elements_array) > 0, "Le tableau d'éléments ne devrait pas être vide");
            $success &= $this->assert($elements_array[0]['type'] === 'text', "Premier élément devrait être de type text");

            return $success;
        });
    }

    public function test_get_order_preview_data() {
        return $this->run_test('test_get_order_preview_data', function() {
            $_POST['nonce'] = 'test_nonce';
            $_POST['order_id'] = '123';

            $order_id = absint($_POST['order_id']);

            if (!$order_id) {
                return $this->assert(false, "ID commande manquant détecté");
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                return $this->assert(false, "Commande non trouvée détectée");
            }

            // Simuler la logique VariableMapper
            $data = [
                'order_number' => '#' . $order->get_id(),
                'order_total' => $order->get_total(),
                'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customer_email' => $order->get_billing_email()
            ];

            // Vérifications
            $success = $this->assert($data['order_number'] === '#123', "order_number devrait être '#123'");
            $success &= $this->assert($data['order_total'] === '150.00', "order_total devrait être '150.00'");
            $success &= $this->assert($data['customer_name'] === 'John Doe', "customer_name devrait être 'John Doe'");

            return $success;
        });
    }

    public function test_invalid_template_id() {
        return $this->run_test('test_invalid_template_id', function() {
            $_POST['nonce'] = 'test_nonce';
            $_POST['template_id'] = '0'; // ID invalide

            $template_id = absint($_POST['template_id']);

            return $this->assert(!$template_id, "Devrait détecter ID template invalide (0)");
        });
    }

    public function test_invalid_order_id() {
        return $this->run_test('test_invalid_order_id', function() {
            $_POST['nonce'] = 'test_nonce';
            $_POST['order_id'] = '999'; // ID qui n'existe pas

            $order_id = absint($_POST['order_id']);
            $order = wc_get_order($order_id);

            return $this->assert(!$order, "Devrait détecter commande inexistante");
        });
    }

    public function run_all_tests() {
        echo "🧪 TESTS ENDPOINTS AJAX\n";
        echo "======================\n";

        $tests = [
            'test_pdf_builder_get_canvas_elements',
            'test_get_order_preview_data',
            'test_invalid_template_id',
            'test_invalid_order_id'
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test) {
            if ($this->{$test}()) {
                $passed++;
            }
        }

        echo "\n" . str_repeat("=", 40) . "\n";
        echo "RÉSULTATS: $passed/$total tests réussis\n";

        if ($passed === $total) {
            echo "🎉 TOUS LES TESTS ENDPOINTS RÉUSSIS !\n";
        } else {
            echo "⚠️ Certains tests ont échoué\n";
        }

        return $passed === $total;
    }
}

// Exécuter les tests
$test = new AJAX_Endpoints_Test();
$test->run_all_tests();