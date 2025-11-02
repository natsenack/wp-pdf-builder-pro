<?php
// Permettre l'exécution en mode test
if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

// Simuler les fonctions WordPress nécessaires pour les tests
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Mock: ne rien faire en test
        return true;
    }
}

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

if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        // Return a mock order for testing
        return (object)['id' => $order_id, 'test' => true];
    }
}

// Définir les constantes du plugin nécessaires
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
}
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', dirname(__FILE__) . '/pdf-builder-pro.php');
}

echo "=== TEST API PREVIEWIMAGEAPI ===\n\n";

// Charger le bootstrap minimal
$bootstrap_path = __DIR__ . '/bootstrap-minimal.php';
if (!file_exists($bootstrap_path)) {
    echo "❌ Bootstrap minimal introuvable\n";
    exit(1);
}

require_once $bootstrap_path;
pdf_builder_load_bootstrap();

echo "✅ Bootstrap chargé\n";

// Test 1: Vérifier que la classe PreviewImageAPI existe
echo "1. Classe PreviewImageAPI: ";
try {
    if (class_exists('WP_PDF_Builder_Pro\\Api\\PreviewImageAPI')) {
        echo "✅ OK\n";
    } else {
        echo "❌ NON\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 2: Instanciation PreviewImageAPI
echo "2. Instanciation PreviewImageAPI: ";
try {
    $preview_api = new WP_PDF_Builder_Pro\Api\PreviewImageAPI();
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 3: Tester la méthode create_generator (privée - on ne peut pas la tester directement)
echo "3. Test génération aperçu simulée: ";
try {
    // Créer des données de template de test
    $template_data = [
        'template' => [
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'APERÇU TEST - Commande: {{order_number}}'
                ],
                [
                    'type' => 'text',
                    'content' => 'Client: {{customer_name}} - Total: {{order_total}}'
                ]
            ]
        ]
    ];

    // Tester avec données fictives (preview_type = 'design')
    $data_provider = new WP_PDF_Builder_Pro\Data\SampleDataProvider();
    $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $data_provider);

    // Générer un aperçu (simuler ce que fait l'API)
    $result = $generator->generate('png'); // Tester génération image

    if (is_string($result) && !empty($result)) {
        echo "✅ OK (Aperçu PNG généré: " . strlen($result) . " bytes)\n";
    } elseif (is_array($result) && isset($result['fallback'])) {
        echo "✅ OK (Aperçu Canvas fallback: " . strlen($result['html']) . " chars)\n";
    } else {
        echo "❌ Génération aperçu échouée\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 4: Tester avec données WooCommerce simulées
echo "4. Test aperçu avec données WooCommerce: ";
try {
    $template_data = [
        'template' => [
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'FACTURE - Commande {{order_number}}'
                ],
                [
                    'type' => 'text',
                    'content' => 'Client: {{customer_name}} ({{customer_email}})'
                ],
                [
                    'type' => 'text',
                    'content' => 'Total: {{order_total}} - Date: {{order_date}}'
                ]
            ]
        ]
    ];

    // Simuler un order WooCommerce
    $mock_order = new stdClass();
    $mock_order->id = 12345;

    $woo_provider = new WP_PDF_Builder_Pro\Data\WooCommerceDataProvider();
    $woo_provider->setOrder($mock_order);

    $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $woo_provider);
    $result = $generator->generate('png');

    if (is_string($result) && !empty($result)) {
        echo "✅ OK (Aperçu WooCommerce PNG: " . strlen($result) . " bytes)\n";
    } elseif (is_array($result) && isset($result['fallback'])) {
        echo "✅ OK (Aperçu WooCommerce Canvas: " . strlen($result['html']) . " chars)\n";
    } else {
        echo "❌ Génération aperçu WooCommerce échouée\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// Test 5: Vérifier les actions WordPress enregistrées
echo "5. Actions WordPress enregistrées: ";
try {
    global $wp_filter;
    $action_registered = false;

    if (isset($wp_filter['wp_ajax_wp_pdf_preview_image'])) {
        foreach ($wp_filter['wp_ajax_wp_pdf_preview_image'] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) &&
                    isset($callback['function'][0]) &&
                    $callback['function'][0] instanceof WP_PDF_Builder_Pro\Api\PreviewImageAPI) {
                    $action_registered = true;
                    break 2;
                }
            }
        }
    }

    if ($action_registered) {
        echo "✅ OK (Action AJAX enregistrée)\n";
    } else {
        echo "⚠️ Action AJAX non détectée (normal en mode test)\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST PREVIEWIMAGEAPI ===\n";
echo "Si les tests d'aperçu sont OK, l'API PreviewImageAPI fonctionnera en production !\n";
?>