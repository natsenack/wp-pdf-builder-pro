<?php
/**
 * WooCommerce Optimization Script
 * Disable unnecessary features to reduce API calls
 */

// Disable WooCommerce Analytics if not needed
add_filter('woocommerce_admin_features', function($features) {
    // Remove analytics features that cause excessive API calls
    $features_to_remove = [
        'analytics',
        'remote-inbox-notifications',
        'coupons',
        'marketing'
    ];

    return array_diff($features, $features_to_remove);
});

// Disable admin notices that trigger API calls
add_action('admin_init', function() {
    remove_action('admin_notices', array(WC_Admin_Notices::class, 'show_notices'));
});

// Reduce heartbeat frequency
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 60; // Increase to 60 seconds
    return $settings;
});

// Disable WooCommerce admin bar
add_filter('woocommerce_show_admin_bar', '__return_false');

// Disable unnecessary WooCommerce scripts on frontend
add_action('wp_enqueue_scripts', function() {
    if (!is_woocommerce() && !is_cart() && !is_checkout()) {
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-cart-fragments');
    }
}, 99);

// Optimize WooCommerce admin
add_action('admin_enqueue_scripts', function($hook) {
    // Only load WooCommerce scripts on WooCommerce pages
    if (strpos($hook, 'woocommerce') === false &&
        strpos($hook, 'wc-') === false &&
        $hook !== 'toplevel_page_wc-admin') {
        wp_dequeue_script('woocommerce_admin');
        wp_dequeue_script('wc-admin-app');
    }
}, 99);