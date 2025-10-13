<?php
/**
 * Plugin Name: PDF Builder Pro - Aggressive Performance Optimizations
 * Description: Aggressive performance optimizations that block WooCommerce analytics API calls
 * Version: 1.0.0
 * Author: PDF Builder Pro
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Aggressive WooCommerce Analytics Blocker
 */
class PDF_Builder_Aggressive_Optimizations {

    /**
     * Initialize aggressive optimizations
     */
    public static function init() {
        // Block WooCommerce analytics at multiple levels
        self::block_analytics_completely();

        // Optimize remaining API calls
        self::optimize_remaining_apis();

        // Performance enhancements
        self::performance_enhancements();
    }

    /**
     * Completely block WooCommerce analytics API calls
     */
    private static function block_analytics_completely() {
        // Block analytics features at the feature level
        add_filter('woocommerce_admin_features', function($features) {
            $blocked = ['analytics', 'remote-inbox-notifications', 'marketing', 'coupons'];
            return array_diff($features, $blocked);
        });

        // Remove analytics REST endpoints completely
        add_filter('rest_endpoints', function($endpoints) {
            $blocked_patterns = [
                '/wc-analytics/',
                '/wc-admin/analytics',
                '/wc-admin/marketing',
                '/wc/v3/reports'
            ];

            foreach ($endpoints as $route => $endpoint) {
                foreach ($blocked_patterns as $pattern) {
                    if (strpos($route, $pattern) !== false) {
                        unset($endpoints[$route]);
                        break;
                    }
                }
            }

            return $endpoints;
        });

        // Block analytics requests at the REST API dispatch level
        add_filter('rest_pre_dispatch', function($result, $server, $request) {
            $route = $request->get_route();

            // Block all analytics-related routes
            $blocked_routes = [
                '/wc-analytics/',
                '/wc-admin/analytics',
                '/wc-admin/marketing',
                '/wc/v3/reports'
            ];

            foreach ($blocked_routes as $blocked_route) {
                if (strpos($route, $blocked_route) !== false) {
                    // Return a cached empty response instead of error
                    $cache_key = 'blocked_' . md5($route);
                    $cached_response = get_transient($cache_key);

                    if ($cached_response === false) {
                        $cached_response = new WP_REST_Response([], 200);
                        set_transient($cache_key, $cached_response, 3600); // Cache for 1 hour
                    }

                    return $cached_response;
                }
            }

            return $result;
        }, 1, 3);

        // Disable WooCommerce tracking completely
        add_filter('woocommerce_tracker_send_override', '__return_true');
        add_filter('woocommerce_allow_tracking', '__return_false');

        // Remove analytics-related admin notices
        add_action('admin_init', function() {
            if (class_exists('WC_Admin_Notices')) {
                remove_action('admin_notices', array(WC_Admin_Notices::class, 'show_notices'));
            }
        });

        // Disable heartbeat on WooCommerce admin pages
        add_filter('heartbeat_settings', function($settings) {
            if (is_admin() && isset($_GET['page'])) {
                $page = $_GET['page'];
                if (strpos($page, 'wc-') === 0 || strpos($page, 'woocommerce') !== false) {
                    $settings['interval'] = 300; // 5 minutes on WC pages
                }
            }
            return $settings;
        });
    }

    /**
     * Optimize remaining WooCommerce API calls
     */
    private static function optimize_remaining_apis() {
        // Cache remaining WooCommerce API calls aggressively
        add_filter('rest_pre_dispatch', function($result, $server, $request) {
            if ($result !== null) return $result; // Already handled

            $route = $request->get_route();

            // Cache other WooCommerce API calls
            if ((strpos($route, '/wc-admin/') === 0 || strpos($route, '/wc/v3/') === 0) &&
                $request->get_method() === 'GET') {

                $cache_key = 'wc_cached_' . md5($route . serialize($request->get_params()));
                $cached_result = get_transient($cache_key);

                if ($cached_result !== false) {
                    return $cached_result;
                }

                // Cache successful responses
                add_filter('rest_post_dispatch', function($response, $server, $request) use ($cache_key) {
                    if ($response->get_status() === 200) {
                        set_transient($cache_key, $response, 600); // 10 minutes
                    }
                    return $response;
                }, 10, 3);
            }

            return $result;
        }, 10, 3);

        // Clear cache on relevant updates
        add_action('save_post', function($post_id) {
            if (get_post_type($post_id) === 'product' || get_post_type($post_id) === 'shop_order') {
                global $wpdb;
                $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_cached_%'");
                $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_blocked_%'");
            }
        });

        add_action('woocommerce_order_status_changed', function() {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_cached_%'");
        });
    }

    /**
     * General performance enhancements
     */
    private static function performance_enhancements() {
        // Optimize script loading
        add_action('wp_enqueue_scripts', function() {
            if (!is_woocommerce() && !is_cart() && !is_checkout()) {
                wp_dequeue_script('woocommerce');
                wp_dequeue_script('wc-cart-fragments');
            }
        }, 99);

        // Optimize admin scripts
        add_action('admin_enqueue_scripts', function($hook) {
            if (strpos($hook, 'woocommerce') === false &&
                strpos($hook, 'wc-') === false &&
                strpos($hook, 'pdf-builder') === false) {
                wp_dequeue_script('woocommerce_admin');
                wp_dequeue_script('wc-admin-app');
            }
        }, 99);

        // Disable unnecessary features
        add_action('init', function() {
            // Disable emojis
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('admin_print_styles', 'print_emoji_styles');

            // Disable embeds
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
        });

        // Optimize database queries
        add_action('wp_scheduled_delete', function() {
            global $wpdb;
            // Clean up expired transients
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_%' AND option_value < UNIX_TIMESTAMP()");
        });
    }
}

// Initialize aggressive optimizations
add_action('muplugins_loaded', function() {
    PDF_Builder_Aggressive_Optimizations::init();
});

// Also initialize on plugins_loaded as fallback
add_action('plugins_loaded', function() {
    if (!did_action('muplugins_loaded')) {
        PDF_Builder_Aggressive_Optimizations::init();
    }
});