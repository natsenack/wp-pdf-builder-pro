<?php
/**
 * WooCommerce function stubs for Intelephense
 * This file contains stubs for WooCommerce functions to satisfy IDE analysis
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

// WooCommerce constants
if (!defined('WC_VERSION')) {
    define('WC_VERSION', '0.0.0');
}