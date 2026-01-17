<?php
/**
 * PDF Builder Pro - REST API Deep Diagnostic & Fallback
 * Diagnostic approfondi et solutions de contournement pour l'API REST
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * Comprehensive REST API diagnostic
 */
function pdf_builder_deep_rest_diagnostic() {
    $diagnostic = array(
        'timestamp' => current_time('mysql'),
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
        'site_url' => site_url(),
        'home_url' => home_url(),
        'tests' => array()
    );

    // Test 1: Basic REST API availability
    $diagnostic['tests']['rest_server'] = array(
        'name' => 'REST Server Object',
        'result' => rest_get_server() !== null,
        'details' => rest_get_server() ? 'Server object exists' : 'Server object is null'
    );

    // Test 2: REST URL generation
    $rest_url = rest_url();
    $diagnostic['tests']['rest_url'] = array(
        'name' => 'REST URL Generation',
        'result' => !empty($rest_url),
        'details' => 'URL: ' . $rest_url
    );

    // Test 3: HTTP request to REST API
    $test_urls = array(
        rest_url() => 'Base REST API',
        rest_url('wp/v2/') => 'WP v2 namespace',
        rest_url('wp/v2/posts') => 'Posts endpoint',
        site_url('/wp-json/wp/v2/') => 'Direct wp-json access',
        home_url('/wp-json/wp/v2/') => 'Home URL wp-json access'
    );

    foreach ($test_urls as $url => $description) {
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'PDF Builder Diagnostic/1.0'
            )
        ));

        $test_result = array(
            'name' => 'HTTP Request: ' . $description,
            'url' => $url,
            'result' => false,
            'response_code' => null,
            'error' => null,
            'headers' => array()
        );

        if (!is_wp_error($response)) {
            $test_result['result'] = wp_remote_retrieve_response_code($response) === 200;
            $test_result['response_code'] = wp_remote_retrieve_response_code($response);
            $test_result['headers'] = wp_remote_retrieve_headers($response);
        } else {
            $test_result['error'] = $response->get_error_message();
        }

        $diagnostic['tests'][] = $test_result;
    }

    // Test 4: Check for blocking plugins/themes
    $diagnostic['tests']['blocking_plugins'] = array(
        'name' => 'Security Plugins Check',
        'result' => true,
        'details' => array()
    );

    $security_plugins = array(
        'wordfence/wordfence.php' => 'Wordfence Security',
        'sucuri-scanner/sucuri.php' => 'Sucuri Security',
        'bulletproof-security/bulletproof-security.php' => 'BulletProof Security',
        'better-wp-security/better-wp-security.php' => 'Better WP Security',
        'all-in-one-wp-security-and-firewall/wp-security-core.php' => 'All In One WP Security',
        'wp-cerber/wp-cerber.php' => 'WP Cerber',
        'limit-login-attempts/limit-login-attempts.php' => 'Limit Login Attempts',
        'login-lockdown/loginlockdown.php' => 'Login LockDown',
        'wp-simple-firewall/icwp-wpsf.php' => 'Shield Security'
    );

    $active_plugins = get_option('active_plugins', array());
    foreach ($security_plugins as $plugin_file => $plugin_name) {
        if (in_array($plugin_file, $active_plugins)) {
            $diagnostic['tests']['blocking_plugins']['details'][] = $plugin_name . ' is active';
        }
    }

    // Test 5: Check wp-config.php constants
    $diagnostic['tests']['wp_config'] = array(
        'name' => 'WP Config Constants',
        'result' => true,
        'details' => array()
    );

    $blocking_constants = array(
        'WP_REST_API' => defined('WP_REST_API') ? WP_REST_API : null,
        'REST_REQUEST' => defined('REST_REQUEST') ? REST_REQUEST : null,
        'DISALLOW_FILE_EDIT' => defined('DISALLOW_FILE_EDIT') ? DISALLOW_FILE_EDIT : null
    );

    foreach ($blocking_constants as $const => $value) {
        if ($value !== null) {
            $diagnostic['tests']['wp_config']['details'][] = $const . ' = ' . var_export($value, true);
        }
    }

    // Test 6: Check .htaccess rules (basic)
    $htaccess_path = ABSPATH . '.htaccess';
    $diagnostic['tests']['htaccess'] = array(
        'name' => '.htaccess File',
        'result' => file_exists($htaccess_path),
        'details' => file_exists($htaccess_path) ? 'File exists' : 'File not found'
    );

    if (file_exists($htaccess_path)) {
        $htaccess_content = file_get_contents($htaccess_path);
        if (strpos($htaccess_content, 'wp-json') !== false) {
            $diagnostic['tests']['htaccess']['details'] .= ' - Contains wp-json rules';
        }
    }

    return $diagnostic;
}

/**
 * Create AJAX fallback for REST API functionality
 */
function pdf_builder_create_ajax_fallback() {
    // Register AJAX handlers that mimic REST API functionality
    add_action('wp_ajax_pdf_builder_rest_fallback', 'pdf_builder_ajax_rest_fallback');
    add_action('wp_ajax_nopriv_pdf_builder_rest_fallback', 'pdf_builder_ajax_rest_fallback');
}

/**
 * AJAX fallback handler
 */
function pdf_builder_ajax_rest_fallback() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $action = isset($_POST['rest_action']) ? sanitize_text_field($_POST['rest_action']) : '';

    switch ($action) {
        case 'get_posts':
            // Fallback for getting posts
            $posts = get_posts(array(
                'numberposts' => 10,
                'post_status' => 'publish'
            ));

            $formatted_posts = array();
            foreach ($posts as $post) {
                $formatted_posts[] = array(
                    'id' => $post->ID,
                    'title' => array('rendered' => $post->post_title),
                    'content' => array('rendered' => $post->post_content),
                    'excerpt' => array('rendered' => $post->post_excerpt),
                    'date' => $post->post_date,
                    'link' => get_permalink($post->ID)
                );
            }

            wp_send_json_success(array('posts' => $formatted_posts));
            break;

        case 'get_users':
            // Fallback for getting users
            if (!current_user_can('list_users')) {
                wp_send_json_error('Insufficient permissions');
                return;
            }

            $users = get_users(array('number' => 10));
            $formatted_users = array();

            foreach ($users as $user) {
                $formatted_users[] = array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'slug' => $user->user_nicename,
                    'avatar_urls' => array(
                        '24' => get_avatar_url($user->ID, array('size' => 24)),
                        '48' => get_avatar_url($user->ID, array('size' => 48)),
                        '96' => get_avatar_url($user->ID, array('size' => 96))
                    )
                );
            }

            wp_send_json_success(array('users' => $formatted_users));
            break;

        case 'test_connection':
            // Simple connection test
            wp_send_json_success(array(
                'status' => 'ok',
                'message' => 'AJAX fallback is working',
                'timestamp' => current_time('mysql'),
                'wp_version' => get_bloginfo('version')
            ));
            break;

        default:
            wp_send_json_error('Unknown action: ' . $action);
            break;
    }
}

/**
 * Override wp.api initialization to use AJAX fallback
 */
function pdf_builder_override_wp_api() {
    // Add inline script to override wp.api initialization
    add_action('admin_enqueue_scripts', function() {
        wp_add_inline_script('jquery', '
            // Override wp.api initialization to prevent REST API calls
            if (typeof window.wp !== "undefined" && typeof window.wp.api !== "undefined") {
                console.log("[PDF Builder] Overriding wp.api to use AJAX fallback");

                // Store original wp.api.init
                var originalApiInit = window.wp.api.init;

                // Override wp.api.init to use our fallback
                window.wp.api.init = function() {
                    console.log("[PDF Builder] wp.api.init overridden - using AJAX fallback");

                    // Create a mock API that uses AJAX instead of REST
                    window.wp.api.models = window.wp.api.models || {};
                    window.wp.api.collections = window.wp.api.collections || {};

                    // Override specific models that might cause issues
                    if (typeof window.wp.api.models.Post !== "undefined") {
                        var originalFetch = window.wp.api.models.Post.prototype.fetch;
                        window.wp.api.models.Post.prototype.fetch = function(options) {
                            console.log("[PDF Builder] Intercepting Post fetch - using AJAX fallback");
                            return pdf_builder_ajax_fallback("get_posts", options);
                        };
                    }

                    // Call original init if it exists
                    if (originalApiInit) {
                        try {
                            originalApiInit.call(this);
                        } catch (e) {
                            console.log("[PDF Builder] Original wp.api.init failed:", e.message);
                        }
                    }
                };
            }

            // AJAX fallback function
            window.pdf_builder_ajax_fallback = function(action, options) {
                options = options || {};
                return jQuery.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "pdf_builder_rest_fallback",
                        rest_action: action,
                        nonce: (typeof pdfBuilderAjax !== "undefined") ? pdfBuilderAjax.nonce : ""
                    },
                    success: function(response) {
                        if (options.success) {
                            options.success(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("[PDF Builder] AJAX fallback failed:", error);
                        if (options.error) {
                            options.error(xhr, status, error);
                        }
                    }
                });
            };
        ', 'after');
    }, 999); // High priority to override after other scripts
}

/**
 * Add diagnostic page
 */
function pdf_builder_add_diagnostic_page() {
    add_submenu_page(
        'pdf-builder-settings',
        'REST API Diagnostic',
        'API Diagnostic',
        'manage_options',
        'pdf-builder-api-diagnostic',
        'pdf_builder_render_diagnostic_page'
    );
}

/**
 * Render diagnostic page
 */
function pdf_builder_render_diagnostic_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    $diagnostic = pdf_builder_deep_rest_diagnostic();

    echo '<div class="wrap">';
    echo '<h1>PDF Builder Pro - REST API Deep Diagnostic</h1>';

    echo '<div class="pdf-builder-diagnostic-results" style="margin-top: 20px;">';

    // Summary
    echo '<div class="diagnostic-summary" style="background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-left: 4px solid #007cba;">';
    echo '<h3>📊 Résumé du diagnostic</h3>';
    echo '<p><strong>WordPress Version:</strong> ' . $diagnostic['wp_version'] . '</p>';
    echo '<p><strong>PHP Version:</strong> ' . $diagnostic['php_version'] . '</p>';
    echo '<p><strong>Site URL:</strong> ' . $diagnostic['site_url'] . '</p>';
    echo '<p><strong>Timestamp:</strong> ' . $diagnostic['timestamp'] . '</p>';
    echo '</div>';

    // Tests results
    echo '<h3>🧪 Résultats des tests</h3>';
    echo '<table class="widefat striped" style="margin-bottom: 20px;">';
    echo '<thead><tr><th>Test</th><th>Résultat</th><th>Détails</th></tr></thead>';
    echo '<tbody>';

    foreach ($diagnostic['tests'] as $test) {
        if (!isset($test['name'])) continue;

        $status_icon = $test['result'] ? '✅' : '❌';
        $status_class = $test['result'] ? 'success' : 'error';

        echo '<tr>';
        echo '<td>' . esc_html($test['name']) . '</td>';
        echo '<td><span style="color: ' . ($test['result'] ? 'green' : 'red') . '; font-weight: bold;">' . $status_icon . '</span></td>';
        echo '<td>';

        if (isset($test['url'])) {
            echo '<strong>URL:</strong> <code>' . esc_html($test['url']) . '</code><br>';
        }

        if (isset($test['response_code'])) {
            echo '<strong>Code réponse:</strong> ' . $test['response_code'] . '<br>';
        }

        if (isset($test['error'])) {
            echo '<strong>Erreur:</strong> ' . esc_html($test['error']) . '<br>';
        }

        if (isset($test['details'])) {
            if (is_array($test['details'])) {
                echo '<strong>Détails:</strong><br>';
                foreach ($test['details'] as $detail) {
                    echo '• ' . esc_html($detail) . '<br>';
                }
            } else {
                echo '<strong>Détails:</strong> ' . esc_html($test['details']);
            }
        }

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    // Recommendations
    echo '<div class="diagnostic-recommendations" style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;">';
    echo '<h3>💡 Recommandations</h3>';
    echo '<ol>';
    echo '<li><strong>Vérifiez les plugins de sécurité:</strong> Désactivez temporairement les plugins de sécurité pour tester</li>';
    echo '<li><strong>Vérifiez les permaliens:</strong> Allez dans Réglages → Permaliens et enregistrez à nouveau</li>';
    echo '<li><strong>Vérifiez .htaccess:</strong> Assurez-vous que les règles de réécriture sont présentes</li>';
    echo '<li><strong>Testez manuellement:</strong> Visitez <code>' . rest_url() . '</code> dans votre navigateur</li>';
    echo '<li><strong>Si rien ne fonctionne:</strong> Le plugin utilisera automatiquement le mode AJAX fallback</li>';
    echo '</ol>';
    echo '</div>';

    // AJAX Test
    echo '<div class="ajax-test" style="margin-top: 20px; padding: 15px; background: #e7f5e7; border-left: 4px solid #28a745;">';
    echo '<h3>🔄 Test AJAX Fallback</h3>';
    echo '<p>Cliquez pour tester si le système de fallback AJAX fonctionne :</p>';
    echo '<button id="test-ajax-fallback" class="button button-primary">Tester AJAX Fallback</button>';
    echo '<div id="ajax-test-result" style="margin-top: 10px; padding: 10px; background: white; border: 1px solid #ddd; display: none;"></div>';
    echo '</div>';

    echo '</div>';

    // Add JavaScript for AJAX test
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#test-ajax-fallback').on('click', function() {
            var $button = $(this);
            var $result = $('#ajax-test-result');

            $button.prop('disabled', true).text('Test en cours...');
            $result.show().html('<p>🔄 Test en cours...</p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_rest_fallback',
                    rest_action: 'test_connection',
                    nonce: '<?php echo wp_create_nonce("pdf_builder_ajax"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<p style="color: green;">✅ AJAX Fallback fonctionne !</p><pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    } else {
                        $result.html('<p style="color: red;">❌ Erreur AJAX: ' + response.data + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $result.html('<p style="color: red;">❌ Erreur de requête AJAX: ' + error + '</p>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Tester AJAX Fallback');
                }
            });
        });
    });
    </script>
    <?php

    echo '</div>';
}

// Initialize everything
add_action('init', 'pdf_builder_create_ajax_fallback', 1);
add_action('init', 'pdf_builder_override_wp_api', 100);
add_action('admin_menu', 'pdf_builder_add_diagnostic_page', 20);

/**
 * Log REST API attempts for debugging
 */
add_action('rest_api_init', function() {
    error_log('[PDF Builder] REST API initialized successfully');
}, 999);

add_filter('rest_pre_dispatch', function($result, $server, $request) {
    error_log('[PDF Builder] REST Request: ' . $request->get_route() . ' - Method: ' . $request->get_method());
    return $result;
}, 1, 3);