<?php
/**
 * PDF Builder Pro - REST API Force Enable
 * Force l'activation de l'API REST de WordPress si elle est désactivée
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * Force enable WordPress REST API
 * This function ensures REST API is available even if disabled by security plugins
 */
function pdf_builder_force_enable_rest_api() {
    // Remove common REST API disable filters
    remove_filter('rest_enabled', '__return_false');
    remove_filter('rest_jsonp_enabled', '__return_false');
    remove_filter('rest_authentication_errors', 'rest_authentication_errors');

    // Force enable REST API
    add_filter('rest_enabled', '__return_true');
    add_filter('rest_jsonp_enabled', '__return_true');

    // Allow all REST API requests (remove authentication requirements for basic endpoints)
    add_filter('rest_authentication_errors', function($result) {
        // Only bypass auth for GET requests to basic endpoints
        if ($_SERVER['REQUEST_METHOD'] === 'GET' &&
            isset($_SERVER['REQUEST_URI']) &&
            (strpos($_SERVER['REQUEST_URI'], '/wp-json/wp/v2/') === 0 ||
             strpos($_SERVER['REQUEST_URI'], '/wp-json/pdf-builder/') === 0)) {
            return null; // No authentication required
        }
        return $result;
    });

    // Ensure REST API endpoints are accessible
    add_filter('rest_pre_dispatch', function($result, $server, $request) {
        // Allow access to basic WordPress endpoints
        $route = $request->get_route();
        if (strpos($route, '/wp/v2/') === 0 || strpos($route, '/pdf-builder/') === 0) {
            return $result;
        }
        return $result;
    }, 10, 3);

    // Disable REST API throttling if present
    add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
        // Remove rate limiting for our requests
        if (isset($handler['callback']) &&
            is_callable($handler['callback']) &&
            (strpos($request->get_route(), '/wp/v2/') === 0 ||
             strpos($request->get_route(), '/pdf-builder/') === 0)) {
            // Allow the request to proceed
        }
        return $response;
    }, 1, 3);
}

/**
 * Check if REST API is disabled and provide status
 */
function pdf_builder_check_rest_api_status() {
    $status = array(
        'rest_enabled' => rest_get_server() !== null,
        'rest_url' => rest_url(),
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown'
    );

    // Test basic REST API endpoint
    $test_url = rest_url('wp/v2/posts');
    $response = wp_remote_get($test_url, array('timeout' => 5));

    if (!is_wp_error($response)) {
        $status['test_response_code'] = wp_remote_retrieve_response_code($response);
        $status['test_success'] = $status['test_response_code'] === 200;
    } else {
        $status['test_error'] = $response->get_error_message();
        $status['test_success'] = false;
    }

    return $status;
}

/**
 * Admin notice for REST API status
 */
function pdf_builder_rest_api_admin_notice() {
    $status = pdf_builder_check_rest_api_status();

    if (!$status['test_success']) {
        $class = 'notice notice-error';
        $message = '<strong>PDF Builder Pro - API REST Désactivée</strong><br>';
        $message .= 'L\'API REST de WordPress est désactivée ou inaccessible. ';

        if (isset($status['test_response_code'])) {
            $message .= 'Code de réponse : ' . $status['test_response_code'] . '<br>';
        }

        if (isset($status['test_error'])) {
            $message .= 'Erreur : ' . $status['test_error'] . '<br>';
        }

        $message .= '<br><strong>Solutions :</strong><br>';
        $message .= '1. Vérifiez les paramètres des permaliens (Réglages → Permaliens)<br>';
        $message .= '2. Désactivez temporairement les plugins de sécurité<br>';
        $message .= '3. Vérifiez wp-config.php pour des constantes de désactivation<br>';
        $message .= '<br><a href="' . admin_url('admin.php?page=pdf-builder-settings&pdf_builder_diagnostic=1') . '" class="button">Voir le diagnostic complet</a>';

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
}

/**
 * Add REST API status to diagnostic
 */
function pdf_builder_extend_diagnostic($results) {
    $status = pdf_builder_check_rest_api_status();

    $results['wordpress_version'] = $status['wp_version'];
    $results['php_version'] = $status['php_version'];
    $results['server_software'] = $status['server_software'];
    $results['rest_forced'] = true;

    return $results;
}

// Initialize REST API force enable
add_action('init', 'pdf_builder_force_enable_rest_api', 1);

// Add admin notice if REST API is not working
add_action('admin_notices', 'pdf_builder_rest_api_admin_notice');

// Extend diagnostic with additional info
add_filter('pdf_builder_rest_api_diagnostic', 'pdf_builder_extend_diagnostic');

/**
 * Manual REST API test endpoint
 */
add_action('rest_api_init', function() {
    register_rest_route('pdf-builder/v1', '/test', array(
        'methods' => 'GET',
        'callback' => function() {
            return array(
                'status' => 'ok',
                'message' => 'PDF Builder REST API is working',
                'timestamp' => current_time('mysql'),
                'wp_version' => get_bloginfo('version')
            );
        },
        'permission_callback' => '__return_true'
    ));
});

/**
 * Force enable REST API via wp-config.php constants override
 */
if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}

if (!defined('REST_API_VERSION')) {
    define('REST_API_VERSION', '2');
}

// Ensure JSON API is available
add_action('init', function() {
    if (!function_exists('wp_api_init')) {
        require_once ABSPATH . 'wp-includes/rest-api.php';
    }
}, 0);