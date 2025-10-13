<?php
/**
 * WooCommerce Optimization Script - Enhanced Version
 * Disable unnecessary features and cache API responses to reduce rate limiting
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
    $settings['interval'] = 120; // Increase to 120 seconds
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

// Cache WooCommerce Analytics API responses
add_filter('rest_pre_dispatch', function($result, $server, $request) {
    // Only cache GET requests to WooCommerce analytics endpoints
    if ($request->get_method() !== 'GET') {
        return $result;
    }

    $route = $request->get_route();
    if (strpos($route, '/wc-analytics/') === 0 || strpos($route, '/wc-admin/') === 0) {
        $cache_key = 'wc_api_' . md5($route . serialize($request->get_params()));
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result;
        }

        // Store the result in cache after processing
        add_filter('rest_post_dispatch', function($response, $server, $request) use ($cache_key) {
            if ($response->get_status() === 200) {
                set_transient($cache_key, $response, 300); // Cache for 5 minutes
            }
            return $response;
        }, 10, 3);
    }

    return $result;
}, 10, 3);

// Disable WooCommerce remote requests that cause rate limiting
add_filter('woocommerce_tracker_send_override', '__return_true');
add_filter('woocommerce_allow_tracking', '__return_false');

// Reduce WooCommerce admin AJAX calls
add_action('admin_init', function() {
    // Disable some WooCommerce admin features that make excessive API calls
    if (isset($_GET['page']) && $_GET['page'] === 'wc-orders') {
        // Reduce the frequency of analytics updates on orders page
        add_filter('woocommerce_admin_order_preview_actions', '__return_empty_array');
    }
});

// Optimize WooCommerce REST API performance
add_filter('woocommerce_rest_api_get_rest_namespaces', function($namespaces) {
    // Remove unused namespaces to reduce overhead
    unset($namespaces['wc-analytics']);
    return $namespaces;
});

// Disable WooCommerce marketing features that cause API calls
add_filter('woocommerce_marketing_recommendations_enabled', '__return_false');
add_filter('woocommerce_admin_features', function($features) {
    return array_diff($features, ['marketing']);
});