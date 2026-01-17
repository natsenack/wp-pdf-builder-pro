<?php
/**
 * PDF Builder Pro - API REST Diagnostic Tool
 * Diagnostic script to check WordPress REST API status
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * Check REST API status and configuration
 */
function pdf_builder_check_rest_api_status() {
    $results = array();

    // Check if REST API is enabled
    $results['rest_api_enabled'] = !defined('REST_REQUEST') || !REST_REQUEST;

    // Check permalink structure
    $permalink_structure = get_option('permalink_structure');
    $results['permalink_structure'] = $permalink_structure;
    $results['permalink_ok'] = !empty($permalink_structure);

    // Check if REST API routes are available
    $rest_url = rest_url();
    $results['rest_url'] = $rest_url;

    // Test basic REST API endpoint
    $response = wp_remote_get($rest_url);
    if (!is_wp_error($response)) {
        $results['rest_api_accessible'] = wp_remote_retrieve_response_code($response) === 200;
        $results['rest_api_response_code'] = wp_remote_retrieve_response_code($response);
    } else {
        $results['rest_api_accessible'] = false;
        $results['rest_api_error'] = $response->get_error_message();
    }

    // Check if our custom API routes are registered
    $results['custom_routes_registered'] = false;
    if (function_exists('rest_get_server')) {
        $server = rest_get_server();
        $routes = $server->get_routes();
        $results['custom_routes_registered'] = isset($routes['/wp-pdf-builder-pro/v1']);
    }

    return $results;
}

/**
 * Display diagnostic results
 */
function pdf_builder_display_rest_api_diagnostic() {
    $results = pdf_builder_check_rest_api_status();

    echo '<div class="pdf-builder-diagnostic">';
    echo '<h3>PDF Builder Pro - REST API Diagnostic</h3>';

    echo '<table class="widefat striped">';
    echo '<thead><tr><th>Check</th><th>Status</th><th>Details</th></tr></thead>';
    echo '<tbody>';

    // REST API Enabled
    $status = $results['rest_api_enabled'] ? '✅ OK' : '❌ Disabled';
    echo '<tr><td>REST API Enabled</td><td>' . $status . '</td><td>WordPress REST API availability</td></tr>';

    // Permalink Structure
    $status = $results['permalink_ok'] ? '✅ OK' : '❌ Not configured';
    $details = $results['permalink_ok'] ? 'Structure: ' . $results['permalink_structure'] : 'Must be set to something other than "Plain"';
    echo '<tr><td>Permalink Structure</td><td>' . $status . '</td><td>' . $details . '</td></tr>';

    // REST API Accessible
    if (isset($results['rest_api_accessible'])) {
        $status = $results['rest_api_accessible'] ? '✅ OK' : '❌ Not accessible';
        $details = 'URL: ' . $results['rest_url'];
        if (!$results['rest_api_accessible']) {
            $details .= '<br>Response code: ' . $results['rest_api_response_code'];
        }
        echo '<tr><td>REST API Accessible</td><td>' . $status . '</td><td>' . $details . '</td></tr>';
    }

    // Custom Routes
    $status = $results['custom_routes_registered'] ? '✅ OK' : '❌ Not registered';
    echo '<tr><td>Custom API Routes</td><td>' . $status . '</td><td>Plugin-specific REST endpoints</td></tr>';

    echo '</tbody></table>';

    // Recommendations
    echo '<h4>Recommendations:</h4>';
    echo '<ul>';

    if (!$results['permalink_ok']) {
        echo '<li><strong>Configure permalinks:</strong> Go to Settings → Permalinks and set to "Post name" or any structure except "Plain"</li>';
    }

    if (isset($results['rest_api_accessible']) && !$results['rest_api_accessible']) {
        echo '<li><strong>Check server configuration:</strong> Ensure mod_rewrite is enabled and .htaccess is writable</li>';
        echo '<li><strong>Check for conflicts:</strong> Disable other plugins temporarily to identify conflicts</li>';
    }

    if (!$results['custom_routes_registered']) {
        echo '<li><strong>Plugin API not loaded:</strong> The plugin\'s REST API routes are not registered. Try reactivating the plugin.</li>';
    }

    echo '<li><strong>Test manually:</strong> Visit <a href="' . rest_url() . '" target="_blank">' . rest_url() . '</a> to test API access</li>';

    echo '</ul>';
    echo '</div>';
}

// Add diagnostic to admin page
add_action('admin_notices', function() {
    if (isset($_GET['pdf_builder_diagnostic'])) {
        pdf_builder_display_rest_api_diagnostic();
    }
});

// Add diagnostic link to plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $diagnostic_link = '<a href="' . admin_url('admin.php?page=pdf-builder-settings&pdf_builder_diagnostic=1') . '">API Diagnostic</a>';
    array_unshift($links, $diagnostic_link);
    return $links;
});