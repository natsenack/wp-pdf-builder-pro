<?php
/**
 * PDF Builder Pro - REST API Server Fix
 * Diagnostic et réparation des problèmes de serveur pour l'API REST
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * Force REST API server initialization
 */
function pdf_builder_force_rest_server_init() {
    // Ensure REST server is initialized early
    if (!class_exists('WP_REST_Server')) {
        require_once ABSPATH . 'wp-includes/rest-api/class-wp-rest-server.php';
    }

    // Force REST API initialization
    add_action('init', function() {
        global $wp_rest_server;
        if (empty($wp_rest_server)) {
            $wp_rest_server = new WP_REST_Server();
            $wp_rest_server->register_route('pdf-builder/v1', '/test', array(
                'methods' => 'GET',
                'callback' => function() {
                    return array('status' => 'forced_rest_working');
                },
                'permission_callback' => '__return_true'
            ));
        }
    }, 0);

    // Force REST API URL handling
    add_filter('rest_url', function($url) {
        // Ensure HTTPS if site is HTTPS
        if (is_ssl() && strpos($url, 'http://') === 0) {
            $url = str_replace('http://', 'https://', $url);
        }
        return $url;
    });

    // Force REST API request handling
    add_action('parse_request', function($wp) {
        if (isset($wp->query_vars['rest_route'])) {
            // Force REST request handling
            define('REST_REQUEST', true);
            define('WP_USE_THEMES', false);

            // Ensure REST server is available
            global $wp_rest_server;
            if (empty($wp_rest_server)) {
                $wp_rest_server = new WP_REST_Server();
                do_action('rest_api_init', $wp_rest_server);
            }
        }
    }, 0);
}

/**
 * Check if mod_rewrite is available
 */
function pdf_builder_check_mod_rewrite() {
    // Check if Apache modules function exists
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        return in_array('mod_rewrite', $modules);
    }

    // Alternative check: try to detect via server signature
    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    if (stripos($server_software, 'apache') !== false) {
        // For Apache, we assume mod_rewrite is available unless proven otherwise
        // This is not 100% accurate but better than nothing
        return true;
    }

    // Check via phpinfo (less reliable)
    ob_start();
    phpinfo(INFO_MODULES);
    $phpinfo = ob_get_clean();
    return stripos($phpinfo, 'mod_rewrite') !== false;
}
function pdf_builder_fix_htaccess_rules() {
    // Check if we can write to .htaccess
    $htaccess_file = ABSPATH . '.htaccess';
    if (!is_writable($htaccess_file)) {
        return false;
    }

    $htaccess_content = file_get_contents($htaccess_file);

    // Check if WordPress rewrite rules exist
    if (strpos($htaccess_content, 'RewriteRule ^index\.php$ - [L]') === false) {
        // Add basic WordPress rewrite rules
        $rewrite_rules = "\n# BEGIN WordPress\n";
        $rewrite_rules .= "<IfModule mod_rewrite.c>\n";
        $rewrite_rules .= "RewriteEngine On\n";
        $rewrite_rules .= "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n";
        $rewrite_rules .= "RewriteBase /\n";
        $rewrite_rules .= "RewriteRule ^index\.php$ - [L]\n";
        $rewrite_rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
        $rewrite_rules .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
        $rewrite_rules .= "RewriteRule . /index.php [L]\n";
        $rewrite_rules .= "</IfModule>\n";
        $rewrite_rules .= "# END WordPress\n";

        // Add to .htaccess if not present
        if (strpos($htaccess_content, '# BEGIN WordPress') === false) {
            $htaccess_content .= $rewrite_rules;
            file_put_contents($htaccess_file, $htaccess_content);
            return 'rewrite_rules_added';
        }
    }

    return true;
}

/**
 * Fix .htaccess rules for REST API access
 */
function pdf_builder_fix_rest_api_htaccess() {
    $htaccess_file = ABSPATH . '.htaccess';

    if (!file_exists($htaccess_file) || !is_writable($htaccess_file)) {
        return false;
    }

    $htaccess_content = file_get_contents($htaccess_file);

    // Check if REST API access rule already exists
    if (strpos($htaccess_content, '# Allow REST API access') !== false) {
        return true; // Already fixed
    }

    // Add rule to allow REST API access before WordPress rules
    $rest_api_rule = "\n# Allow REST API access\n";
    $rest_api_rule .= "<IfModule mod_rewrite.c>\n";
    $rest_api_rule .= "RewriteRule ^wp-json/?(.*) /index.php?rest_route=/$1 [L]\n";
    $rest_api_rule .= "</IfModule>\n";
    $rest_api_rule .= "\n";

    // Insert before # BEGIN WordPress
    if (strpos($htaccess_content, '# BEGIN WordPress') !== false) {
        $htaccess_content = str_replace('# BEGIN WordPress', $rest_api_rule . '# BEGIN WordPress', $htaccess_content);
    } else {
        // Add at the end if no WordPress section found
        $htaccess_content .= $rest_api_rule;
    }

    $result = file_put_contents($htaccess_file, $htaccess_content);
    return $result !== false;
}

/**
 * Test direct REST API access
 */
function pdf_builder_test_direct_rest_access() {
    // Test direct file access to see if WordPress handles REST requests
    $test_results = array();

    // Test 1: Direct index.php access with REST parameters
    $direct_url = home_url('/index.php?rest_route=/wp/v2/');
    $response = wp_remote_get($direct_url, array('timeout' => 10));

    $test_results['direct_index_php'] = array(
        'url' => $direct_url,
        'success' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200,
        'response_code' => !is_wp_error($response) ? wp_remote_retrieve_response_code($response) : 'error',
        'content_length' => !is_wp_error($response) ? strlen(wp_remote_retrieve_body($response)) : 0
    );

    // Test 2: Check if permalinks are working
    $permalink_test = get_permalink(get_option('page_on_front', 1));
    if ($permalink_test) {
        $response = wp_remote_get($permalink_test, array('timeout' => 5));
        $test_results['permalink_test'] = array(
            'url' => $permalink_test,
            'success' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200,
            'response_code' => !is_wp_error($response) ? wp_remote_retrieve_response_code($response) : 'error'
        );
    }

    // Test 3: Check server configuration
    $test_results['server_info'] = array(
        'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
        'php_version' => PHP_VERSION,
        'mod_rewrite' => function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : 'unknown',
        'https' => is_ssl(),
        'site_url' => site_url(),
        'home_url' => home_url()
    );

    return $test_results;
}

/**
 * Force REST API endpoint registration
 */
function pdf_builder_force_rest_endpoints() {
    add_action('rest_api_init', function() {
        // Register basic WordPress endpoints if missing
        $routes = rest_get_server()->get_routes();
        if (!isset($routes['/wp/v2/posts'])) {
            register_rest_route('wp/v2', '/posts', array(
                'methods' => 'GET',
                'callback' => function() {
                    $posts = get_posts(array('numberposts' => 10));
                    return array_map(function($post) {
                        return array(
                            'id' => $post->ID,
                            'title' => array('rendered' => $post->post_title),
                            'content' => array('rendered' => $post->post_content),
                            'date' => $post->post_date,
                            'link' => get_permalink($post->ID)
                        );
                    }, $posts);
                },
                'permission_callback' => '__return_true'
            ));
        }

        // Register basic root endpoint
        register_rest_route('pdf-builder/v1', '/?', array(
            'methods' => 'GET',
            'callback' => function() {
                return array(
                    'name' => get_bloginfo('name'),
                    'description' => get_bloginfo('description'),
                    'url' => get_bloginfo('url'),
                    'home' => home_url(),
                    'namespaces' => rest_get_server()->get_namespaces(),
                    'routes' => array_keys(rest_get_server()->get_routes())
                );
            },
            'permission_callback' => '__return_true'
        ));

    }, 1);
}

/**
 * Admin notice with server fix options
 */
function pdf_builder_rest_server_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Only show if REST API is not working
    $test_url = rest_url();
    $response = wp_remote_get($test_url, array('timeout' => 5));

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        return; // REST API is working
    }

    $class = 'notice notice-warning is-dismissible';
    $message = '<strong>PDF Builder Pro - API REST Server Issue</strong><br>';
    $message .= 'L\'API REST retourne des erreurs 404. Voici les solutions possibles :<br><br>';

    $message .= '<strong>🔧 Solutions automatiques :</strong><br>';
    $message .= '<a href="' . admin_url('admin.php?page=pdf-builder-api-diagnostic&fix_htaccess=1') . '" class="button button-primary" style="margin-right: 10px;">Réparer .htaccess</a>';
    $message .= '<a href="' . admin_url('admin.php?page=pdf-builder-api-diagnostic&force_endpoints=1') . '" class="button button-secondary">Forcer les endpoints</a><br><br>';

    $message .= '<strong>🔍 Solutions manuelles :</strong><br>';
    $message .= '1. <strong>Permaliens :</strong> Allez dans Réglages → Permaliens et cliquez "Enregistrer"<br>';
    $message .= '2. <strong>.htaccess :</strong> Vérifiez que les règles de réécriture WordPress sont présentes<br>';
    $message .= '3. <strong>Serveur :</strong> Assurez-vous que mod_rewrite est activé<br>';
    $message .= '4. <strong>SSL :</strong> Vérifiez la configuration HTTPS<br><br>';

    $message .= '<a href="' . admin_url('admin.php?page=pdf-builder-api-diagnostic') . '">Voir le diagnostic complet</a>';

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
}

/**
 * Handle fix actions
 */
function pdf_builder_handle_fix_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Fix .htaccess
    if (isset($_GET['fix_htaccess'])) {
        $result = pdf_builder_fix_htaccess_rules();
        $rest_result = pdf_builder_fix_rest_api_htaccess();
        if ($result === 'rewrite_rules_added' || $rest_result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Règles de réécriture et accès REST API ajoutés à .htaccess. Testez maintenant l\'API REST.</p></div>';
            });
        } elseif ($result === false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Impossible d\'écrire dans .htaccess. Vérifiez les permissions.</p></div>';
            });
        }
        // Redirect to remove the parameter
        wp_redirect(remove_query_arg('fix_htaccess'));
        exit;
    }

    // Force endpoints
    if (isset($_GET['force_endpoints'])) {
        pdf_builder_force_rest_endpoints();
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Endpoints REST forcés. Testez maintenant l\'API REST.</p></div>';
        });
        wp_redirect(remove_query_arg('force_endpoints'));
        exit;
    }
}

// Initialize all fixes
add_action('init', 'pdf_builder_force_rest_server_init', 0);
add_action('init', 'pdf_builder_force_rest_endpoints', 1);
add_action('admin_init', 'pdf_builder_handle_fix_actions');
add_action('admin_notices', 'pdf_builder_rest_server_admin_notice');

// Extend diagnostic with server tests
add_filter('pdf_builder_rest_api_diagnostic', function($results) {
    $server_tests = pdf_builder_test_direct_rest_access();
    $results['server_tests'] = $server_tests;
    $results['htaccess_fixed'] = pdf_builder_fix_htaccess_rules();
    $results['rest_api_htaccess_fixed'] = pdf_builder_fix_rest_api_htaccess();
    $results['mod_rewrite_enabled'] = pdf_builder_check_mod_rewrite();
    return $results;
});