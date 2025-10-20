<?php
/**
 * Bootstrap for PHPUnit tests
 */

// Allow access for testing
define('PHPUNIT_RUNNING', true);
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Mock essential WordPress functions BEFORE loading autoloader
if (!function_exists('trailingslashit')) {
    function trailingslashit($string) {
        return rtrim($string, '/\\') . '/';
    }
}

// Mock WooCommerce global function
if (!function_exists('WC')) {
    function WC() {
        // Return a mock WooCommerce object
        return new class() {
            public $countries;
            public function __construct() {
                $this->countries = new class() {
                    public function get_countries() {
                        return [
                            'FR' => 'France',
                            'US' => 'United States',
                            'GB' => 'United Kingdom',
                            'DE' => 'Germany',
                            'ES' => 'Spain',
                            'IT' => 'Italy'
                        ];
                    }
                };
            }
        };
    }
}

// Load the autoloader if it exists
if (file_exists(dirname(__DIR__) . '/core/autoloader.php')) {
    require_once dirname(__DIR__) . '/core/autoloader.php';
}

// Load test utilities
require_once dirname(__DIR__) . '/stubs.php';

// Mock additional WordPress functions if not available
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return true; // Always pass for tests
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Always allow for tests
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

if (!function_exists('get_option')) {
    function get_option($key, $default = null) {
        return $default;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return 'test_nonce_' . $action;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        throw new Exception($message);
    }
}

// Mock WooCommerce functions
if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        // Return a mock order object for testing
        return new class($order_id) {
            private $id;
            public function __construct($id) { $this->id = $id; }
            public function get_id() { return $this->id; }
            public function get_order_number() { return '#' . $this->id; }
            public function get_date_created() { return new DateTime('2025-10-20'); }
            public function get_date_modified() { return new DateTime('2025-10-20'); }
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
                    new class {
                        public function get_name() { return 'Test Product'; }
                        public function get_quantity() { return 2; }
                        public function get_total() { return '100.00'; }
                        public function get_subtotal() { return '80.00'; }
                        public function get_total_tax() { return '20.00'; }
                        public function get_product_id() { return 1; }
                        public function get_variation_id() { return 0; }
                        public function get_sku() { return 'TEST-SKU'; }
                    }
                ];
            }
        };
    }
}

if (!function_exists('wc_get_order_statuses')) {
    function wc_get_order_statuses() {
        return [
            'wc-pending' => 'En attente',
            'wc-processing' => 'En cours',
            'wc-on-hold' => 'En attente',
            'wc-completed' => 'Terminée',
            'wc-cancelled' => 'Annulée',
            'wc-refunded' => 'Remboursée',
            'wc-failed' => 'Échouée'
        ];
    }
}

if (!function_exists('wc_price')) {
    function wc_price($price, $args = []) {
        return number_format($price, 2, ',', ' ') . ' €';
    }
}