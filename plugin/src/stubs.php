<?php
/**
 * Stubs for external functions and constants to satisfy Intelephense
 * This file is for development only and should not be deployed to production
 */

// WooCommerce functions
if (!function_exists('wc_get_order')) {
    /**
     * @param int $order_id
     * @return mixed
     */
    function wc_get_order($order_id) { return null; }
}

if (!function_exists('wc_get_order_statuses')) {
    /**
     * @return array
     */
    function wc_get_order_statuses() { return []; }
}

if (!function_exists('wc_price')) {
    /**
     * @param float $price
     * @return string
     */
    function wc_price($price) { return ''; }
}

if (!function_exists('wc_get_order_status_name')) {
    /**
     * @param string $status
     * @return string
     */
    function wc_get_order_status_name($status) { return ''; }
}

if (!function_exists('wc_get_product')) {
    /**
     * @param int $product_id
     * @return mixed
     */
    function wc_get_product($product_id) { return null; }
}

if (!function_exists('get_woocommerce_currency')) {
    /**
     * @return string
     */
    function get_woocommerce_currency() { return ''; }
}

// WordPress constants
if (!defined('WP_CLI')) {
    define('WP_CLI', false);
}

if (!defined('WC_VERSION')) {
    define('WC_VERSION', '0.0.0');
}

if (!defined('DISABLE_WP_CRON')) {
    define('DISABLE_WP_CRON', false);
}

// Custom functions
if (!function_exists('pdf_builder_is_woocommerce_active')) {
    /**
     * @return bool
     */
    function pdf_builder_is_woocommerce_active() { return false; }
}

if (!function_exists('pdf_builder_run_migrations')) {
    /**
     * @param string $version
     */
    function pdf_builder_run_migrations($version) {}
}

// PHP native functions that Intelephense might not recognize
if (!function_exists('rand')) {
    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    function rand($min = 0, $max = PHP_INT_MAX) { return 0; }
}



