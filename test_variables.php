<?php
/**
 * Test rapide des variables dans l'aperçu
 */

// Simuler un environnement WordPress basique
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($key) {
        return 'Test Company';
    }
}

if (!function_exists('get_option')) {
    function get_option($key, $default = '') {
        $options = [
            'pdf_builder_company_email' => 'test@company.com',
            'pdf_builder_company_phone' => '+33123456789',
            'pdf_builder_company_siret' => '12345678900012',
            'pdf_builder_company_address' => '123 Test Street',
            'pdf_builder_company_city' => 'Test City',
            'pdf_builder_company_postcode' => '75001',
        ];
        return $options[$key] ?? $default;
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($content) {
        return $content;
    }
}

// Simuler un ordre WooCommerce
class MockOrder {
    public function get_id() { return 123; }
    public function get_order_number() { return 'WC-123'; }
    public function get_date_created() { return new DateTime('2025-10-19 12:00:00'); }
    public function get_billing_first_name() { return 'Jean'; }
    public function get_billing_last_name() { return 'Dupont'; }
    public function get_billing_email() { return 'jean@example.com'; }
    public function get_billing_phone() { return '+33123456789'; }
    public function get_billing_company() { return 'Test Company'; }
    public function get_billing_address_1() { return '123 Rue Test'; }
    public function get_billing_address_2() { return ''; }
    public function get_billing_city() { return 'Paris'; }
    public function get_billing_state() { return ''; }
    public function get_billing_postcode() { return '75001'; }
    public function get_billing_country() { return 'FR'; }
    public function get_formatted_billing_address() { return "Jean Dupont\n123 Rue Test\n75001 Paris\nFrance"; }
    public function get_shipping_first_name() { return 'Jean'; }
    public function get_shipping_last_name() { return 'Dupont'; }
    public function get_shipping_company() { return ''; }
    public function get_shipping_address_1() { return '123 Rue Test'; }
    public function get_shipping_address_2() { return ''; }
    public function get_shipping_city() { return 'Paris'; }
    public function get_shipping_state() { return ''; }
    public function get_shipping_postcode() { return '75001'; }
    public function get_shipping_country() { return 'FR'; }
    public function get_formatted_shipping_address() { return "Jean Dupont\n123 Rue Test\n75001 Paris\nFrance"; }
    public function get_total() { return 99.99; }
    public function get_subtotal() { return 89.99; }
    public function get_total_tax() { return 10.00; }
    public function get_shipping_total() { return 5.00; }
    public function get_discount_total() { return 0.00; }
    public function get_payment_method_title() { return 'Carte bancaire'; }
    public function get_status() { return 'processing'; }
    public function get_currency() { return 'EUR'; }
}

// Inclure le contrôleur
require_once __DIR__ . '/src/Controllers/PDF_Generator_Controller.php';

// Créer un générateur
$generator = new PDF_Builder_Pro_Generator();
$order = new MockOrder();
$generator->set_order($order);

// Tester le remplacement
$test_content = '{{company_name}} - {{order_id}} - {{customer_name}} - {{total}}';
$result = $generator->replace_order_variables($test_content, $order);

echo "Test de remplacement des variables :\n";
echo "Original: $test_content\n";
echo "Résultat: $result\n";

// Tester avec un élément
$element = [
    'type' => 'dynamic-text',
    'content' => '{{company_name}} - Commande #{{order_id}} de {{customer_name}}'
];

echo "\nTest avec élément dynamic-text :\n";
echo "Contenu original: " . $element['content'] . "\n";

// Simuler render_element_to_html
$content = $element['content'];
if ($generator->getOrder()) {
    $content = $generator->replace_order_variables($content, $order);
}
echo "Contenu traité: $content\n";

echo "\nTest terminé.\n";