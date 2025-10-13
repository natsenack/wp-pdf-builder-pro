<?php
/**
 * Plugin Name: PDF Builder Pro - Performance Optimizations
 * Description: Performance optimizations for PDF Builder Pro and WooCommerce
 * Version: 1.0.0
 * Author: PDF Builder Pro
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PDF Builder Pro Performance Optimizations
 */
class PDF_Builder_Performance_Optimizations {

    /**
     * Initialize optimizations
     */
    public static function init() {
        // WooCommerce optimizations
        self::optimize_woocommerce();

        // WordPress optimizations
        self::optimize_wordpress();

        // Cache optimizations
        self::optimize_caching();
    }

    /**
     * Optimize WooCommerce performance
     */
    private static function optimize_woocommerce() {
        // Disable analytics features that cause excessive API calls
        add_filter('woocommerce_admin_features', function($features) {
            $features_to_remove = [
                'analytics',
                'remote-inbox-notifications',
                'marketing'
            ];
            return array_diff($features, $features_to_remove);
        });

        // Disable admin notices
        add_action('admin_init', function() {
            if (class_exists('WC_Admin_Notices')) {
                remove_action('admin_notices', array(WC_Admin_Notices::class, 'show_notices'));
            }
        });

        // Reduce heartbeat frequency
        add_filter('heartbeat_settings', function($settings) {
            $settings['interval'] = 120; // 2 minutes
            return $settings;
        });

        // Disable tracking
        add_filter('woocommerce_tracker_send_override', '__return_true');
        add_filter('woocommerce_allow_tracking', '__return_false');

        // Cache WooCommerce API responses
        add_filter('rest_pre_dispatch', function($result, $server, $request) {
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

                add_filter('rest_post_dispatch', function($response, $server, $request) use ($cache_key) {
                    if ($response->get_status() === 200) {
                        set_transient($cache_key, $response, 300); // 5 minutes cache
                    }
                    return $response;
                }, 10, 3);
            }

            return $result;
        }, 10, 3);
    }

    /**
     * Optimize WordPress performance
     */
    private static function optimize_wordpress() {
        // Optimize script loading
        add_action('wp_enqueue_scripts', function() {
            if (!is_woocommerce() && !is_cart() && !is_checkout()) {
                wp_dequeue_script('woocommerce');
                wp_dequeue_script('wc-cart-fragments');
            }
        }, 99);

        // Optimize admin script loading
        add_action('admin_enqueue_scripts', function($hook) {
            if (strpos($hook, 'woocommerce') === false &&
                strpos($hook, 'wc-') === false &&
                strpos($hook, 'pdf-builder') === false &&
                $hook !== 'toplevel_page_wc-admin') {
                wp_dequeue_script('woocommerce_admin');
                wp_dequeue_script('wc-admin-app');
            }
        }, 99);
    }

    /**
     * Optimize caching
     */
    private static function optimize_caching() {
        // Clear WooCommerce API cache on relevant actions
        add_action('save_post', function($post_id) {
            if (get_post_type($post_id) === 'product') {
                global $wpdb;
                $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_api_%'");
            }
        });

        add_action('woocommerce_order_status_changed', function() {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_api_%'");
        });
    }
}

// Initialize optimizations
add_action('plugins_loaded', function() {
    PDF_Builder_Performance_Optimizations::init();
});