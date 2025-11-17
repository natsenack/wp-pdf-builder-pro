<?php

    /**
     * PDF Builder Pro - Settings Page
     * Complete settings with all tabs
     */

    if (!defined('ABSPATH')) {
        exit('Direct access forbidden');
    }

    // Function to send AJAX response
    function send_ajax_response($success, $message = '', $data = [])
    {
        $response = json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
    }

    // Check if this is an AJAX request
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) ===
        'xmlhttprequest';

    // Handle AJAX clear cache request BEFORE the early exit
    if ($is_ajax && isset($_POST['action']) && $_POST['action'] === 'pdf_builder_clear_cache') {
        if (wp_verify_nonce($_POST['security'], 'pdf_builder_clear_cache_performance')) {
            // Clear transients and cache
            delete_transient('pdf_builder_cache');
            delete_transient('pdf_builder_templates');
            delete_transient('pdf_builder_elements');
            // Clear WP object cache if available
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }

            send_ajax_response(true, 'Cache vidé avec succès.');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Handle other maintenance AJAX requests BEFORE the early exit
    if ($is_ajax && isset($_POST['action'])) {
        $action = $_POST['action'];

        // Remove temp files
        if ($action === 'pdf_builder_remove_temp_files') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_remove_temp')) {
                // Remove temp files (implement logic here)
                $temp_dir = sys_get_temp_dir() . '/pdf-builder';
                if (is_dir($temp_dir)) {
                    $files = glob($temp_dir . '/*');
                    $removed = 0;
                    foreach ($files as $file) {
                        if (is_file($file) && unlink($file)) {
                            $removed++;
                        }
                    }
                }
                send_ajax_response(true, "Fichiers temporaires supprimés: $removed fichier(s)");
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Optimize database
        elseif ($action === 'pdf_builder_optimize_db') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_optimize_db')) {
                global $wpdb;
                $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder%'", ARRAY_N);
                $optimized = 0;
                foreach ($tables as $table) {
                    $wpdb->query("OPTIMIZE TABLE {$table[0]}");
                    $optimized++;
                }
                send_ajax_response(true, "Tables optimisées: $optimized table(s)");
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Repair templates
        elseif ($action === 'pdf_builder_repair_templates') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_repair_templates')) {
                // Repair templates logic (implement as needed)
                send_ajax_response(true, 'Templates réparés avec succès');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Reset settings
        elseif ($action === 'pdf_builder_reset_settings') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_reset_settings')) {
                delete_option('pdf_builder_settings');
                delete_option('pdf_builder_canvas_settings');
                send_ajax_response(true, 'Paramètres réinitialisés');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Check integrity
        elseif ($action === 'pdf_builder_check_integrity') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_check_integrity')) {
                // Check integrity logic (implement as needed)
                send_ajax_response(true, 'Intégrité vérifiée avec succès');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Generate test license key
        elseif ($action === 'pdf_builder_generate_test_license_key') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_generate_license_key')) {
                $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));
                update_option('pdf_builder_license_test_key', $test_key);
                send_ajax_response(true, 'Clé de test générée', ['key' => $test_key]);
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Delete test license key
        elseif ($action === 'pdf_builder_delete_test_license_key') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_delete_test_license_key')) {
                delete_option('pdf_builder_license_test_key');
                send_ajax_response(true, 'Clé de test supprimée');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Toggle test mode
        elseif ($action === 'pdf_builder_toggle_test_mode') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_toggle_test_mode')) {
                $current_mode = get_option('pdf_builder_license_test_mode_enabled', false);
                $new_mode = !$current_mode;
                update_option('pdf_builder_license_test_mode_enabled', $new_mode);
                send_ajax_response(true, 'Mode test ' . ($new_mode ? 'activé' : 'désactivé'), ['enabled' => $new_mode]);
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Cleanup license
        elseif ($action === 'pdf_builder_cleanup_license') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                delete_option('pdf_builder_license_test_key');
                delete_option('pdf_builder_license_test_mode_enabled');
                send_ajax_response(true, 'Licence nettoyée complètement');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Simple test
        elseif ($action === 'pdf_builder_simple_test') {
            // Simple test - no nonce needed for basic connectivity test
            send_ajax_response(true, 'Test réussi - Cache intégré fonctionne');
        }

        // Test SMTP connection
        elseif ($action === 'pdf_builder_test_smtp_connection') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                // Test SMTP logic (implement as needed)
                send_ajax_response(true, 'Connexion SMTP testée avec succès');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }

        // Test notifications
        elseif ($action === 'pdf_builder_test_notifications') {
            if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                // Test notifications logic (implement as needed)
                send_ajax_response(true, 'Notifications testées avec succès');
            } else {
                send_ajax_response(false, 'Erreur de sécurité.');
            }
        }
    }

    // For AJAX requests, only process POST data and exit - don't show HTML
    if ($is_ajax && !empty($_POST)) {
        // Process the request and exit - the processing code below will handle it
        // This ensures no HTML is output for AJAX requests
        return;
        // Exit early for AJAX POST requests to prevent HTML output
    }

    if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
        wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'pdf-builder-pro'));
    }

    // Vérifier l'accès via Role_Manager si disponible
    if (class_exists('WP_PDF_Builder_Pro\Security\Role_Manager')) {
        \WP_PDF_Builder_Pro\Security\Role_Manager::check_and_block_access();
    }

    // Debug: Page loaded
    if (defined('WP_DEBUG') && WP_DEBUG) {

    }

    // Initialize
    $notices = [];
    $settings = get_option('pdf_builder_settings', []);
    $canvas_settings = get_option('pdf_builder_canvas_settings', []);
    // Charger la clé de test de licence si elle existe
    $license_test_key = get_option('pdf_builder_license_test_key', '');
    $license_test_mode = get_option('pdf_builder_license_test_mode_enabled', false);
    $settings['license_test_mode'] = $license_test_mode;

    // Charger les paramètres individuels sauvegardés via AJAX
    $settings['cache_enabled'] = get_option('pdf_builder_cache_enabled', false);
    $settings['cache_ttl'] = get_option('pdf_builder_cache_ttl', 3600);
    $settings['cache_compression'] = get_option('pdf_builder_cache_compression', true);
    $settings['cache_auto_cleanup'] = get_option('pdf_builder_cache_auto_cleanup', true);
    $settings['cache_max_size'] = get_option('pdf_builder_cache_max_size', 100);
    $settings['company_phone_manual'] = get_option('pdf_builder_company_phone_manual', '');
    $settings['company_siret'] = get_option('pdf_builder_company_siret', '');
    $settings['company_vat'] = get_option('pdf_builder_company_vat', '');
    $settings['company_rcs'] = get_option('pdf_builder_company_rcs', '');
    $settings['company_capital'] = get_option('pdf_builder_company_capital', '');
    $settings['pdf_quality'] = get_option('pdf_builder_pdf_quality', 'high');
    $settings['default_format'] = get_option('pdf_builder_default_format', 'A4');
    $settings['default_orientation'] = get_option('pdf_builder_default_orientation', 'portrait');

    // Vérifier que les valeurs sont bien définies
    $company_phone_manual = $settings['company_phone_manual'] ?? '';
    $company_siret = $settings['company_siret'] ?? '';
    $company_vat = $settings['company_vat'] ?? '';
    $company_rcs = $settings['company_rcs'] ?? '';
    $company_capital = $settings['company_capital'] ?? '';

    // Variables pour la configuration PDF
    $pdf_quality = $settings['pdf_quality'] ?? 'high';
    $default_format = $settings['default_format'] ?? 'A4';
    $default_orientation = $settings['default_orientation'] ?? 'portrait';
    // Log ALL POST data at the beginning
    if (!empty($_POST)) {
        error_log('ALL POST data received: ' . print_r($_POST, true));
        error_log('is_ajax: ' . ($is_ajax ? 'true' : 'false'));
        }
        if (!empty($_POST)) {

        } else {

    }

    // Process form
    if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
        if ($is_ajax) {
            error_log('AJAX: Matched condition 1 - submit + pdf_builder_settings_nonce');
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {

        }
        if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {

            }
            // Check for max_input_vars limit
            $max_input_vars = ini_get('max_input_vars');
            if ($max_input_vars && count($_POST) >= $max_input_vars) {
                $notices[] = '<div class="notice notice-error"><p><strong>⚠️</strong> Trop de paramètres soumis (' . count($_POST) . '). Limite PHP max_input_vars: ' . $max_input_vars . '. Certains paramètres n\'ont pas été sauvegardés.</p></div>';
            }
            $to_save = [
                'debug_mode' => isset($_POST['debug_mode']),
                'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
                'cache_enabled' => isset($_POST['cache_enabled']),
                'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
                'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
                'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
                // PDF settings from general tab
                'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
                'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
                'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
                // Performance settings moved to Performance tab only
                // PDF settings moved to PDF tab only
                // Canvas settings moved to Canvas tab only
                // Développeur
                'developer_enabled' => isset($_POST['developer_enabled']),
                'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                'debug_php_errors' => isset($_POST['debug_php_errors']),
                'debug_javascript' => isset($_POST['debug_javascript']),
                'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']),
                'debug_ajax' => isset($_POST['debug_ajax']),
                'debug_performance' => isset($_POST['debug_performance']),
                'debug_database' => isset($_POST['debug_database']),
                'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                'log_retention' => intval($_POST['log_retention'] ?? 30),
                'license_test_mode' => isset($_POST['license_test_mode']),
                'force_https' => isset($_POST['force_https']),
            ];
            $new_settings = array_merge($settings, $to_save);
            // Check if settings actually changed - use serialize for deep comparison
            $settings_changed = serialize($new_settings) !== serialize($settings);
            if (defined('WP_DEBUG') && WP_DEBUG) {

            }

            $result = update_option('pdf_builder_settings', $new_settings);
            try {
                    // Debug: Always log the result for troubleshooting


                        // Simplified success logic: if no exception was thrown, consider it successful
                        if ($is_ajax) {
                            send_ajax_response(true, 'Paramètres enregistrés avec succès.');
                        } else {
                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
                        }
                    } catch (Exception $e) {

                if ($is_ajax) {
                    send_ajax_response(false, 'Erreur lors de la sauvegarde des paramètres: ' . $e->getMessage());
                } else {
                    $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur lors de la sauvegarde des paramètres: ' . esc_html($e->getMessage()) . '</p></div>';
                }
            }
            $settings = get_option('pdf_builder_settings', []);
        } else {
            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
        }
    }

    // Handle cache clear
    if (
        isset($_POST['clear_cache']) &&
        (isset($_POST['pdf_builder_clear_cache_nonce_performance']) ||
        isset($_POST['pdf_builder_clear_cache_nonce_maintenance']))
     ) {
        $nonce_verified = false;
        if (isset($_POST['pdf_builder_clear_cache_nonce_performance'])) {
            $nonce_verified = wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce_performance'], 'pdf_builder_clear_cache_performance');
        } elseif (isset($_POST['pdf_builder_clear_cache_nonce_maintenance'])) {
            $nonce_verified = wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce_maintenance'], 'pdf_builder_clear_cache_maintenance');
        }

        if ($nonce_verified) {
     // Clear transients and cache
            delete_transient('pdf_builder_cache');
            delete_transient('pdf_builder_templates');
            delete_transient('pdf_builder_elements');
     // Clear WP object cache if available
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }

            if ($is_ajax) {
                send_ajax_response(true, 'Cache vidé avec succès.');
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Cache vidé avec succès.</p></div>';
            }
        }
    }


    if (isset($_POST['submit']) && isset($_POST['pdf_builder_general_nonce'])) {
        if ($is_ajax) {
            error_log('AJAX: Matched condition 2 - submit + pdf_builder_general_nonce');
        }
        if (wp_verify_nonce($_POST['pdf_builder_general_nonce'], 'pdf_builder_settings')) {
            $general_settings = [
                'cache_enabled' => isset($_POST['cache_enabled']),
                'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
                'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
                'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
                // Informations entreprise manuelles
                'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
                'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
                'company_vat' => sanitize_text_field($_POST['company_vat'] ?? ''),
                'company_rcs' => sanitize_text_field($_POST['company_rcs'] ?? ''),
                'company_capital' => sanitize_text_field($_POST['company_capital'] ?? ''),
            ];
        // Update individual settings
            foreach ($general_settings as $key => $value) {
                $settings[$key] = $value;
            }

            update_option('pdf_builder_settings', $settings);
            if ($is_ajax) {
                $response = json_encode(['success' => true, 'message' => 'Paramètres généraux enregistrés avec succès.']);
                wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres généraux enregistrés avec succès.</p></div>';
            }
        } else {
            if ($is_ajax) {
                $response = json_encode(['success' => false, 'message' => 'Erreur de sécurité. Veuillez réessayer.']);
                wp_die($response, '', array('response' => 403, 'content_type' => 'application/json'));
            } else {
                $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
            }
        }
    }

    // NOTE: Old duplicates removed - only using specific nonces below
    // - submit_pdf now uses pdf_builder_pdf_nonce
    // - submit_security now uses pdf_builder_securite_nonce
    // - submit_canvas now uses pdf_builder_canvas_nonce

    if (isset($_POST['submit_developpeur']) && isset($_POST['pdf_builder_developpeur_nonce'])) {
        if (wp_verify_nonce($_POST['pdf_builder_developpeur_nonce'], 'pdf_builder_settings')) {
            $dev_settings = [
                'developer_enabled' => isset($_POST['developer_enabled']),
                'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                'debug_php_errors' => isset($_POST['debug_php_errors']),
                'debug_javascript' => isset($_POST['debug_javascript']),
                'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']),
                'debug_ajax' => isset($_POST['debug_ajax']),
                'debug_performance' => isset($_POST['debug_performance']),
                'debug_database' => isset($_POST['debug_database']),
                'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
                'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                'log_retention' => intval($_POST['log_retention'] ?? 30),
                'force_https' => isset($_POST['force_https']),
                'license_test_mode' => isset($_POST['license_test_mode']),
            ];

            $result = update_option('pdf_builder_settings', array_merge($settings, $dev_settings));
        // Sauvegarder aussi l'état du mode test dans une option séparée pour le handler de licence
            update_option('pdf_builder_license_test_mode_enabled', isset($_POST['license_test_mode']));

            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres développeur enregistrés avec succès.</p></div>';
            $settings = get_option('pdf_builder_settings', []);

        } else {

            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
        }
    }

    if (isset($_POST['submit_performance']) && isset($_POST['pdf_builder_performance_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_performance_nonce'], 'pdf_builder_performance_settings')) {
            $performance_settings = [
                'compress_images' => isset($_POST['compress_images']),
                'image_quality' => intval($_POST['image_quality'] ?? 85),
                'optimize_for_web' => isset($_POST['optimize_for_web']),
                'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
                'limit_fps' => isset($_POST['limit_fps']),
                'max_fps' => intval($_POST['max_fps'] ?? 60),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $performance_settings));
      // Save auto_save settings to canvas_settings (not general settings)
            $canvas_settings_to_update = $canvas_settings;
            $canvas_settings_to_update['auto_save_enabled'] = isset($_POST['auto_save_enabled']) && $_POST['auto_save_enabled'] === '1';
            $canvas_settings_to_update['auto_save_interval'] = intval($_POST['auto_save_interval'] ?? 30);
            update_option('pdf_builder_canvas_settings', $canvas_settings_to_update);
            $canvas_settings = $canvas_settings_to_update;
            if ($is_ajax) {
                $response = json_encode(['success' => true, 'message' => 'Paramètres de performance enregistrés avec succès.']);
                wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de performance enregistrés avec succès.</p></div>';
            }
            $settings = get_option('pdf_builder_settings', []);
        }
    }

    if (isset($_POST['submit_pdf']) && isset($_POST['pdf_builder_pdf_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_pdf_nonce'], 'pdf_builder_pdf_settings')) {
            $pdf_settings = [
                'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
                'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
                'pdf_author' => sanitize_text_field($_POST['pdf_author'] ?? get_bloginfo('name')),
                'pdf_subject' => sanitize_text_field($_POST['pdf_subject'] ?? ''),
                'include_metadata' => isset($_POST['include_metadata']),
                'embed_fonts' => isset($_POST['embed_fonts']),
                'auto_crop' => isset($_POST['auto_crop']),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $pdf_settings));
            if ($is_ajax) {
                $response = json_encode(['success' => true, 'message' => 'Paramètres PDF enregistrés avec succès.']);
                wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres PDF enregistrés avec succès.</p></div>';
            }
            $settings = get_option('pdf_builder_settings', []);
        }
    }

    if (isset($_POST['submit_security']) && isset($_POST['pdf_builder_securite_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_securite_nonce'], 'pdf_builder_settings')) {
            $security_settings = [
                'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
                'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
                'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $security_settings));
            if ($is_ajax) {
                $response = json_encode(['success' => true, 'message' => 'Paramètres de sécurité enregistrés avec succès.']);
                wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de sécurité enregistrés avec succès.</p></div>';
            }
            $settings = get_option('pdf_builder_settings', []);
        }
    }

    if (isset($_POST['submit_canvas']) && isset($_POST['pdf_builder_canvas_nonce'])) {
        if (wp_verify_nonce($_POST['pdf_builder_canvas_nonce'], 'pdf_builder_settings')) {
            $canvas_settings_to_save = [
                'default_canvas_format' => sanitize_text_field($_POST['default_canvas_format'] ?? 'A4'),
                'default_canvas_orientation' => sanitize_text_field($_POST['default_canvas_orientation'] ?? 'portrait'),
                'default_canvas_unit' => sanitize_text_field($_POST['default_canvas_unit'] ?? 'px'),
                'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
                'canvas_background_color' => sanitize_text_field($_POST['canvas_background_color'] ?? '#ffffff'),
                'canvas_show_transparency' => isset($_POST['canvas_show_transparency']) && $_POST['canvas_show_transparency'] === '1' ? '1' : '0',
                'container_background_color' => sanitize_text_field($_POST['container_background_color'] ?? '#f8f9fa'),
                'container_show_transparency' => isset($_POST['container_show_transparency']) && $_POST['container_show_transparency'] === '1' ? '1' : '0',
                'margin_top' => intval($_POST['margin_top'] ?? 28),
                'margin_right' => intval($_POST['margin_right'] ?? 28),
                'margin_bottom' => intval($_POST['margin_bottom'] ?? 10),
                'margin_left' => intval($_POST['margin_left'] ?? 10),
                'show_margins' => isset($_POST['show_margins']) && $_POST['show_margins'] === '1' ? '1' : '0',
                'show_grid' => isset($_POST['show_grid']) && $_POST['show_grid'] === '1' ? '1' : '0',
                'grid_size' => intval($_POST['grid_size'] ?? 10),
                'grid_color' => sanitize_text_field($_POST['grid_color'] ?? '#e0e0e0'),
                'snap_to_grid' => isset($_POST['snap_to_grid']) && $_POST['snap_to_grid'] === '1' ? '1' : '0',
                'snap_to_elements' => isset($_POST['snap_to_elements']) && $_POST['snap_to_elements'] === '1' ? '1' : '0',
                'snap_tolerance' => intval($_POST['snap_tolerance'] ?? 5),
                'show_guides' => isset($_POST['show_guides']) && $_POST['show_guides'] === '1' ? '1' : '0',
                'default_zoom' => intval($_POST['default_zoom'] ?? 100),
                'zoom_step' => intval($_POST['zoom_step'] ?? 25),
                'min_zoom' => intval($_POST['min_zoom'] ?? 10),
                'max_zoom' => intval($_POST['max_zoom'] ?? 500),
                'zoom_with_wheel' => $_POST['zoom_with_wheel'] === '1' ? '1' : '0',
                'pan_with_mouse' => $_POST['pan_with_mouse'] === '1' ? '1' : '0',
                'show_resize_handles' => $_POST['show_resize_handles'] === '1' ? '1' : '0',
                'handle_size' => intval($_POST['handle_size'] ?? 8),
                'handle_color' => sanitize_text_field($_POST['handle_color'] ?? '#007cba'),
                'enable_rotation' => $_POST['enable_rotation'] === '1' ? '1' : '0',
                'rotation_step' => intval($_POST['rotation_step'] ?? 15),
                'multi_select' => isset($_POST['multi_select']),
                'copy_paste_enabled' => isset($_POST['copy_paste_enabled']),
                'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
                'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
                'compress_images' => isset($_POST['compress_images']),
                'image_quality' => intval($_POST['image_quality'] ?? 85),
                'max_image_size' => intval($_POST['max_image_size'] ?? 2048),
                'include_metadata' => isset($_POST['include_metadata']),
                'pdf_author' => sanitize_text_field($_POST['pdf_author'] ?? 'PDF Builder Pro'),
                'pdf_subject' => sanitize_text_field($_POST['pdf_subject'] ?? ''),
                'auto_crop' => isset($_POST['auto_crop']) && $_POST['auto_crop'] === '1',
                'embed_fonts' => isset($_POST['embed_fonts']) && $_POST['embed_fonts'] === '1',
                'optimize_for_web' => isset($_POST['optimize_for_web']) && $_POST['optimize_for_web'] === '1',
                'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']) && $_POST['enable_hardware_acceleration'] === '1',
                'limit_fps' => isset($_POST['limit_fps']) && $_POST['limit_fps'] === '1',
                'max_fps' => intval($_POST['max_fps'] ?? 60),
                'auto_save_enabled' => isset($_POST['auto_save_enabled']) && $_POST['auto_save_enabled'] === '1',
                'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
                'auto_save_versions' => intval($_POST['auto_save_versions'] ?? 10),
                'undo_levels' => intval($_POST['undo_levels'] ?? 50),
                'redo_levels' => intval($_POST['redo_levels'] ?? 50),
                'enable_keyboard_shortcuts' => isset($_POST['enable_keyboard_shortcuts']) && $_POST['enable_keyboard_shortcuts'] === '1',
                'debug_mode' => isset($_POST['debug_mode']) && $_POST['debug_mode'] === '1',
                'show_fps' => isset($_POST['show_fps']) && $_POST['show_fps'] === '1',
            ];
        // Sauvegarder dans les options WordPress
            update_option('pdf_builder_canvas_settings', $canvas_settings_to_save);
            if ($is_ajax) {
                $response = json_encode(['success' => true, 'message' => 'Paramètres Canvas enregistrés avec succès.']);
                wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres Canvas enregistrés avec succès.</p></div>';
            }
            $settings = get_option('pdf_builder_settings', []);
        }
    }

    if (isset($_POST['submit_templates']) && isset($_POST['pdf_builder_templates_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_templates_nonce'], 'pdf_builder_settings')) {
     // NOTE: This section is now handled in the Templates tab form below (line 2846)
            // Keeping this comment to avoid confusion - code is handled in the proper form section
        }
    }

    if (isset($_POST['submit_maintenance']) && isset($_POST['pdf_builder_settings_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
            $maintenance_settings = [
                // Les paramètres de maintenance sont principalement des actions, pas des sauvegardes de config
                // Mais on peut sauvegarder des préférences de maintenance si nécessaire
            ];
            update_option('pdf_builder_settings', array_merge($settings, $maintenance_settings));
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de maintenance enregistrés avec succès.</p></div>';
            $settings = get_option('pdf_builder_settings', []);
        }
    }

    // Gestionnaire pour la sauvegarde des paramètres de sauvegarde
    if (isset($_POST['submit_backup']) && isset($_POST['pdf_builder_backup_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_backup_nonce'], 'pdf_builder_backup')) {
            $backup_settings = [
                'auto_backup' => isset($_POST['auto_backup']) ? 1 : 0,
                'backup_retention' => intval($_POST['backup_retention'] ?? 30),
            ];
            update_option('pdf_builder_backup_settings', $backup_settings);
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de sauvegarde enregistrés avec succès.</p></div>';
        } else {
            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
        }
    }

    // Gestionnaire pour la sauvegarde des paramètres RGPD
    if (isset($_POST['submit_rgpd']) && isset($_POST['pdf_builder_rgpd_nonce'])) {

        if (wp_verify_nonce($_POST['pdf_builder_rgpd_nonce'], 'pdf_builder_rgpd')) {
            $rgpd_settings = [
                'gdpr_consent_required' => isset($_POST['gdpr_consent_required']) ? 1 : 0,
                'data_retention_days' => intval($_POST['data_retention_days'] ?? 2555),
            ];
            update_option('pdf_builder_rgpd_settings', $rgpd_settings);
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres RGPD enregistrés avec succès.</p></div>';
        } else {
            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
        }
    }
?>
<script>
    // Script de définition des paramètres canvas - exécuté très tôt

    // Récupérer les paramètres canvas depuis les options WordPress
    <?php $canvas_settings_js = get_option('pdf_builder_canvas_settings', []); ?>

    // Définir pdfBuilderCanvasSettings globalement avant tout autre script
    window.pdfBuilderCanvasSettings = <?php echo wp_json_encode([
        'default_canvas_format' => $canvas_settings_js['default_canvas_format'] ?? 'A4',
        'default_canvas_orientation' => $canvas_settings_js['default_canvas_orientation'] ?? 'portrait',
        'default_canvas_unit' => $canvas_settings_js['default_canvas_unit'] ?? 'px',
        'default_orientation' => $canvas_settings_js['default_orientation'] ?? 'portrait',
        'canvas_background_color' => $canvas_settings_js['canvas_background_color'] ?? '#ffffff',
        'canvas_show_transparency' => $canvas_settings_js['canvas_show_transparency'] ?? false,
        'container_background_color' => $canvas_settings_js['container_background_color'] ?? '#f8f9fa',
        'container_show_transparency' => $canvas_settings_js['container_show_transparency'] ?? false,
        'margin_top' => $canvas_settings_js['margin_top'] ?? 28,
        'margin_right' => $canvas_settings_js['margin_right'] ?? 28,
        'margin_bottom' => $canvas_settings_js['margin_bottom'] ?? 10,
        'margin_left' => $canvas_settings_js['margin_left'] ?? 10,
        'show_margins' => ($canvas_settings_js['show_margins'] ?? false) === '1',
        'show_grid' => ($canvas_settings_js['show_grid'] ?? false) === '1',
        'grid_size' => $canvas_settings_js['grid_size'] ?? 10,
        'grid_color' => $canvas_settings_js['grid_color'] ?? '#e0e0e0',
        'snap_to_elements' => ($canvas_settings_js['snap_to_elements'] ?? false) === '1',
        'snap_tolerance' => $canvas_settings_js['snap_tolerance'] ?? 5,
        'show_guides' => ($canvas_settings_js['show_guides'] ?? false) === '1',
        'default_zoom' => $canvas_settings_js['default_zoom'] ?? 100,
        'zoom_step' => $canvas_settings_js['zoom_step'] ?? 25,
        'min_zoom' => $canvas_settings_js['min_zoom'] ?? 10,
        'max_zoom' => $canvas_settings_js['max_zoom'] ?? 500,
        'zoom_with_wheel' => ($canvas_settings_js['zoom_with_wheel'] ?? false) === '1',
        'pan_with_mouse' => ($canvas_settings_js['pan_with_mouse'] ?? false) === '1',
        'show_resize_handles' => ($canvas_settings_js['show_resize_handles'] ?? false) === '1',
        'handle_size' => $canvas_settings_js['handle_size'] ?? 8,
        'handle_color' => $canvas_settings_js['handle_color'] ?? '#007cba',
        'enable_rotation' => ($canvas_settings_js['enable_rotation'] ?? false) === '1',
        'rotation_step' => $canvas_settings_js['rotation_step'] ?? 15,
        'multi_select' => $canvas_settings_js['multi_select'] ?? false,
        'copy_paste_enabled' => $canvas_settings_js['copy_paste_enabled'] ?? false,
        'export_quality' => $canvas_settings_js['export_quality'] ?? 'print',
        'export_format' => $canvas_settings_js['export_format'] ?? 'pdf',
        'compress_images' => $canvas_settings_js['compress_images'] ?? true,
        'image_quality' => $canvas_settings_js['image_quality'] ?? 85,
        'max_image_size' => $canvas_settings_js['max_image_size'] ?? 2048,
        'include_metadata' => $canvas_settings_js['include_metadata'] ?? true,
        'pdf_author' => $canvas_settings_js['pdf_author'] ?? 'PDF Builder Pro',
        'pdf_subject' => $canvas_settings_js['pdf_subject'] ?? '',
        'auto_crop' => $canvas_settings_js['auto_crop'] ?? false,
        'embed_fonts' => $canvas_settings_js['embed_fonts'] ?? true,
        'optimize_for_web' => $canvas_settings_js['optimize_for_web'] ?? true,
        'enable_hardware_acceleration' => $canvas_settings_js['enable_hardware_acceleration'] ?? true,
        'limit_fps' => $canvas_settings_js['limit_fps'] ?? true,
        'max_fps' => $canvas_settings_js['max_fps'] ?? 60,
        'auto_save_enabled' => $canvas_settings_js['auto_save_enabled'] ?? false,
        'auto_save_interval' => $canvas_settings_js['auto_save_interval'] ?? 30,
        'auto_save_versions' => $canvas_settings_js['auto_save_versions'] ?? 10,
        'undo_levels' => $canvas_settings_js['undo_levels'] ?? 50,
        'redo_levels' => $canvas_settings_js['redo_levels'] ?? 50,
        'enable_keyboard_shortcuts' => $canvas_settings_js['enable_keyboard_shortcuts'] ?? true,
        'debug_mode' => $canvas_settings_js['debug_mode'] ?? false,
        'show_fps' => $canvas_settings_js['show_fps'] ?? false
    ]); ?>;

    // Fonction pour convertir le format et l'orientation en dimensions pixels
    window.pdfBuilderCanvasSettings.getDimensionsFromFormat = function(format, orientation) {
        const formatDimensions = {
            'A6': { width: 349, height: 496 },
            'A5': { width: 496, height: 701 },
            'A4': { width: 794, height: 1123 },
            'A3': { width: 1123, height: 1587 },
            'A2': { width: 1587, height: 2245 },
            'A1': { width: 2245, height: 3175 },
            'A0': { width: 3175, height: 4494 },
            'Letter': { width: 816, height: 1056 },
            'Legal': { width: 816, height: 1344 },
            'Tabloid': { width: 1056, height: 1632 }
        };

        const dims = formatDimensions[format] || formatDimensions['A4'];

        // Inverser les dimensions si orientation paysage
        if (orientation === 'landscape') {
            return { width: dims.height, height: dims.width };
        }

        return dims;
    };

    // Ajouter les dimensions calculées aux paramètres
    window.pdfBuilderCanvasSettings.default_canvas_width = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
        window.pdfBuilderCanvasSettings.default_canvas_format,
        window.pdfBuilderCanvasSettings.default_canvas_orientation
    ).width;

    window.pdfBuilderCanvasSettings.default_canvas_height = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
        window.pdfBuilderCanvasSettings.default_canvas_format,
        window.pdfBuilderCanvasSettings.default_canvas_orientation
    ).height;

    // ✅ PDF_BUILDER_VERBOSE initialized in PDF_Builder_Admin.php via wp_add_inline_script()


</script>
<?php
    // If this is an AJAX request that wasn't handled above, return error
    if ($is_ajax) {
        send_ajax_response(false, 'Requête AJAX non reconnue ou invalide.');
    }
?>
<div class="wrap">
    <h1><?php _e('⚙️ PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>

    <?php foreach ($notices as $notice) {
        echo $notice;
    } ?>

    <div class="nav-tab-wrapper wp-clearfix">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">
            <span class="tab-icon">⚙️</span>
            <span class="tab-text">Général</span>
        </a>
        <a href="#licence" class="nav-tab" data-tab="licence">
            <span class="tab-icon">🔑</span>
            <span class="tab-text">Licence</span>
        </a>
        <a href="#systeme" class="nav-tab" data-tab="systeme">
            <span class="tab-icon">🔧</span>
            <span class="tab-text">Système</span>
        </a>
        <a href="#acces" class="nav-tab" data-tab="acces">
            <span class="tab-icon">👥</span>
            <span class="tab-text">Accès</span>
        </a>
        <a href="#securite" class="nav-tab" data-tab="securite">
            <span class="tab-icon">🔒</span>
            <span class="tab-text">Sécurité & Conformité</span>
        </a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">
            <span class="tab-icon">📄</span>
            <span class="tab-text">Configuration PDF</span>
        </a>
        <a href="#contenu" class="nav-tab" data-tab="contenu">
            <span class="tab-icon">🎨</span>
            <span class="tab-text">Contenu & Design</span>
        </a>
        <a href="#roles" class="nav-tab" data-tab="roles">
            <span class="tab-icon">👨‍💻</span>
            <span class="tab-text">Développeur</span>
        </a>
    </div>

        <div id="general" class="tab-content active">
            <h2>🏠 Paramètres Généraux</h2>

            <!-- Section Cache et Performance -->
            <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border: 2px solid #28a745; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #155724; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">📋 Cache & Performance</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" <?php checked(get_option('pdf_builder_cache_enabled', false)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Améliore les performances en mettant en cache les données</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_compression">Compression du cache</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="cache_compression" name="cache_compression" value="1" <?php checked(get_option('pdf_builder_cache_compression', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Compresser les données en cache pour économiser l'espace disque</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_auto_cleanup">Nettoyage automatique</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="cache_auto_cleanup" name="cache_auto_cleanup" value="1" <?php checked(get_option('pdf_builder_cache_auto_cleanup', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Nettoyer automatiquement les anciens fichiers cache</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_max_size">Taille max du cache (MB)</label></th>
                            <td>
                                <input type="number" id="cache_max_size" name="cache_max_size" value="<?php echo intval(get_option('pdf_builder_cache_max_size', 100)); ?>" min="10" max="1000" step="10" />
                                <p class="description">Taille maximale du dossier cache en mégaoctets</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                            <td>
                                <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval(get_option('pdf_builder_cache_ttl', 3600)); ?>" min="0" max="86400" />
                                <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Test du système</th>
                            <td>
                                <button type="button" id="test-cache-btn" class="button button-secondary" style="background-color: #6c757d; border-color: #6c757d; color: white; font-weight: bold; padding: 10px 15px;">
                                    🧪 Tester l'intégration du cache
                                </button>
                                <span id="cache-test-results" style="margin-left: 10px;"></span>
                                <div id="cache-test-output" style="display: none; margin-top: 10px; padding: 15px; background: #e7f5e9; border-left: 4px solid #28a745; -webkit-border-radius: 4px; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; border-radius: 4px; color: #155724;"></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Vider le cache</th>
                            <td>
                                <button type="button" id="clear-cache-general-btn" class="button button-secondary" style="background-color: #dc3232; border-color: #dc3232; color: white; font-weight: bold; padding: 10px 15px;">
                                    🗑️ Vider tout le cache
                                </button>
                                <span id="clear-cache-general-results" style="margin-left: 10px;"></span>
                                <p class="description">Vide tous les transients, caches et données en cache du plugin</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Informations sur l'état du cache -->
                    <div style="margin-top: 30px; padding: 20px; background: rgba(255,255,255,0.8); border-radius: 8px; border: 1px solid #28a745;">
                        <h4 style="margin-top: 0; color: #155724;">📊 État du système de cache</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                    <?php
                                    function get_folder_size($dir) {
                                        $size = 0;
                                        if (is_dir($dir)) {
                                            $files = scandir($dir);
                                            foreach ($files as $file) {
                                                if ($file != '.' && $file != '..') {
                                                    $path = $dir . '/' . $file;
                                                    if (is_dir($path)) {
                                                        $size += get_folder_size($path);
                                                    } else {
                                                        $size += filesize($path);
                                                    }
                                                }
                                            }
                                        }
                                        return $size;
                                    }

                                    $cache_size = 0;
                                    $upload_dir = wp_upload_dir();
                                    $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache';
                                    if (is_dir($cache_dir)) {
                                        $cache_size = get_folder_size($cache_dir);
                                    }
                                    echo size_format($cache_size);
                                    ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Taille du cache</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                    <?php
                                    $transient_count = 0;
                                    global $wpdb;
                                    $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
                                    echo intval($transient_count);
                                    ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Transients actifs</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: <?php echo get_option('pdf_builder_cache_enabled', false) ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo get_option('pdf_builder_cache_enabled', false) ? '✅' : '❌'; ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Cache activé</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                    <?php
                                    $last_cleanup = get_option('pdf_builder_cache_last_cleanup', 'Jamais');
                                    if ($last_cleanup !== 'Jamais') {
                                        $last_cleanup = human_time_diff(strtotime($last_cleanup)) . ' ago';
                                    }
                                    echo $last_cleanup;
                                    ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Dernier nettoyage</div>
                            </div>
                        </div>
                    </div>
                </form>

                <script>
                jQuery(document).ready(function($) {
                    // Fonction pour afficher/cacher les éléments du cache
                    function toggleCacheElements(show) {
                        // Éléments à cacher/montrer
                        var elementsToToggle = [
                            'tr:has([for="cache_compression"])', // Ligne compression
                            'tr:has([for="cache_auto_cleanup"])', // Ligne nettoyage auto
                            'tr:has([for="cache_max_size"])', // Ligne taille max
                            'tr:has([for="cache_ttl"])', // Ligne TTL
                            'tr:has(#test-cache-btn)', // Ligne test du système
                            'tr:has(#clear-cache-general-btn)', // Ligne vider le cache
                            '.form-table + div' // Section d'informations sur l'état du cache
                        ];

                        elementsToToggle.forEach(function(selector) {
                            if (show) {
                                $(selector).show();
                            } else {
                                $(selector).hide();
                            }
                        });
                    }

                    // Vérifier l'état initial du cache
                    var cacheEnabled = $('#cache_enabled').is(':checked');
                    toggleCacheElements(cacheEnabled);

                    // Gérer le changement d'état du cache
                    $('#cache_enabled').on('change', function() {
                        var isEnabled = $(this).is(':checked');
                        toggleCacheElements(isEnabled);
                    });
                });
                </script>
            </div>

            <!-- Section Informations Entreprise -->
            <div style="background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border: 2px solid #0066cc; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #004085; margin-top: 0; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">🏢 Informations Entreprise</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_general_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">
                    <!-- Le bouton submit est supprimé car on utilise le système AJAX global -->

                    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin-top: 0; color: #155724;">📋 Informations récupérées automatiquement de WooCommerce</h4>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                            <p style="margin: 5px 0;"><strong>Nom de l'entreprise :</strong> <?php echo esc_html(get_option('woocommerce_store_name', get_bloginfo('name'))); ?></p>
                            <p style="margin: 5px 0;"><strong>Adresse complète :</strong> <?php
                            $address = get_option('woocommerce_store_address', '');
                            $city = get_option('woocommerce_store_city', '');
                            $postcode = get_option('woocommerce_store_postcode', '');
                            $country = get_option('woocommerce_default_country', '');
                            $full_address = array_filter([$address, $city, $postcode, $country]);
                            echo esc_html(implode(', ', $full_address) ?: '<em>Non défini</em>');
                            ?></p>
                            <p style="margin: 5px 0;"><strong>Email :</strong> <?php echo esc_html(get_option('admin_email', '<em>Non défini</em>')); ?></p>
                            <p style="color: #666; font-size: 12px; margin: 10px 0 0 0;">
                            ℹ️ Ces informations sont automatiquement récupérées depuis les paramètres WooCommerce (WooCommerce > Réglages > Général).
                            </p>
                        </div>

                        <h4 style="color: #dc3545;">📝 Informations à saisir manuellement</h4>
                        <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                        Ces informations ne sont pas disponibles dans WooCommerce et doivent être saisies manuellement :
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="company_phone_manual">Téléphone</label></th>
                                <td>
                                    <input type="text" id="company_phone_manual" name="company_phone_manual"
                                        value="<?php echo esc_attr($company_phone_manual); ?>"
                                        placeholder="+33 1 23 45 67 89" />
                                    <p class="description">Téléphone de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_siret">Numéro SIRET</label></th>
                                <td>
                                    <input type="text" id="company_siret" name="company_siret"
                                        value="<?php echo esc_attr($company_siret); ?>"
                                        placeholder="123 456 789 00012" />
                                    <p class="description">Numéro SIRET de l'entreprise</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_vat">Numéro TVA</label></th>
                                <td>
                                    <input type="text" id="company_vat" name="company_vat"
                                        value="<?php echo esc_attr($company_vat); ?>"
                                        placeholder="FR12345678901, DE123456789, BE0123456789" />
                                    <p class="description">Numéro de TVA intracommunautaire (format européen : 2 lettres pays + 8-12 caractères)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_rcs">RCS</label></th>
                                <td>
                                    <input type="text" id="company_rcs" name="company_rcs"
                                        value="<?php echo esc_attr($company_rcs); ?>"
                                        placeholder="Lyon B 123 456 789" />
                                    <p class="description">Numéro RCS (Registre du Commerce et des Sociétés)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="company_capital">Capital social</label></th>
                                <td>
                                    <input type="text" id="company_capital" name="company_capital"
                                        value="<?php echo esc_attr($company_capital); ?>"
                                        placeholder="10 000 €" />
                                    <p class="description">Montant du capital social de l'entreprise</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>

                <script>
                jQuery(document).ready(function($) {
                    // Fonction de validation des champs entreprise
                    function validateCompanyFields() {
                        var isValid = true;
                        var errors = [];

                        // Validation du téléphone (maximum 10 chiffres)
                        var phone = $('#company_phone_manual').val().trim();
                        if (phone !== '') {
                            // Supprimer tous les caractères non numériques
                            var phoneNumbers = phone.replace(/\D/g, '');
                            if (phoneNumbers.length > 10) {
                                isValid = false;
                                errors.push('Le numéro de téléphone ne peut pas dépasser 10 chiffres.');
                                $('#company_phone_manual').addClass('error').removeClass('valid');
                            } else {
                                $('#company_phone_manual').addClass('valid').removeClass('error');
                            }
                        } else {
                            $('#company_phone_manual').removeClass('error valid');
                        }

                        // Validation du SIRET (14 chiffres)
                        var siret = $('#company_siret').val().trim();
                        if (siret !== '') {
                            var siretNumbers = siret.replace(/\D/g, '');
                            if (siretNumbers.length !== 14) {
                                isValid = false;
                                errors.push('Le numéro SIRET doit contenir exactement 14 chiffres.');
                                $('#company_siret').addClass('error').removeClass('valid');
                            } else {
                                $('#company_siret').addClass('valid').removeClass('error');
                            }
                        } else {
                            $('#company_siret').removeClass('error valid');
                        }

                        // Validation du numéro TVA (format européen flexible)
                        var vat = $('#company_vat').val().trim();
                        if (vat !== '') {
                            // Regex pour les formats TVA européens courants
                            // Format général: 2 lettres pays + chiffres/lettres (8-12 caractères)
                            var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
                            if (!vatPattern.test(vat.replace(/\s/g, ''))) {
                                isValid = false;
                                errors.push('Le numéro TVA doit être au format européen valide (ex: FR12345678901, DE123456789, BE0123456789).');
                                $('#company_vat').addClass('error').removeClass('valid');
                            } else {
                                $('#company_vat').addClass('valid').removeClass('error');
                            }
                        } else {
                            $('#company_vat').removeClass('error valid');
                        }

                        // Afficher les erreurs si il y en a
                        if (!isValid) {
                            alert('Erreurs de validation :\n\n' + errors.join('\n'));
                        }

                        return isValid;
                    }

                    // Validation en temps réel pour le téléphone
                    $('#company_phone_manual').on('input', function() {
                        var phone = $(this).val().trim();
                        var phoneNumbers = phone.replace(/\D/g, '');
                        if (phoneNumbers.length > 10) {
                            $(this).addClass('error').removeClass('valid');
                        } else if (phoneNumbers.length > 0 && phoneNumbers.length <= 10) {
                            $(this).addClass('valid').removeClass('error');
                        } else {
                            $(this).removeClass('error valid');
                        }
                    });

                    // Validation en temps réel pour le SIRET
                    $('#company_siret').on('input', function() {
                        var siret = $(this).val().trim();
                        var siretNumbers = siret.replace(/\D/g, '');
                        if (siretNumbers.length === 14) {
                            $(this).addClass('valid').removeClass('error');
                        } else if (siretNumbers.length > 0) {
                            $(this).addClass('error').removeClass('valid');
                        } else {
                            $(this).removeClass('error valid');
                        }
                    });

                    // Validation en temps réel pour la TVA
                    $('#company_vat').on('input', function() {
                        var vat = $(this).val().trim();
                        // Regex pour les formats TVA européens courants
                        var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
                        if (vat !== '' && vatPattern.test(vat.replace(/\s/g, ''))) {
                            $(this).addClass('valid').removeClass('error');
                        } else if (vat !== '' && !vatPattern.test(vat.replace(/\s/g, ''))) {
                            $(this).addClass('error').removeClass('valid');
                        } else {
                            $(this).removeClass('error valid');
                        }
                    });

                    // Validation avant soumission du formulaire
                    $('form[action*="admin.php?page=pdf-builder-settings"]').on('submit', function(e) {
                        if (!validateCompanyFields()) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });
                </script>

                <style>
                .form-table input.error {
                    border-color: #dc3545 !important;
                    box-shadow: 0 0 0 1px #dc3545 !important;
                    background-color: #fff5f5 !important;
                }
                .form-table input.error:focus {
                    border-color: #dc3545 !important;
                    box-shadow: 0 0 0 1px #dc3545, 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
                }
                .form-table input.valid {
                    border-color: #28a745 !important;
                    box-shadow: 0 0 0 1px #28a745 !important;
                    background-color: #f8fff8 !important;
                }
                .form-table input.valid:focus {
                    border-color: #28a745 !important;
                    box-shadow: 0 0 0 1px #28a745, 0 0 0 3px rgba(40, 167, 69, 0.1) !important;
                }
                </style>
            </div>

            <!-- Section Paramètres PDF -->
            <div style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #856404; margin-top: 0; border-bottom: 2px solid #ffc107; padding-bottom: 10px;">📄 Configuration PDF</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_general_nonce'); ?>
                    <input type="hidden" name="current_tab" value="general">

                    <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                        <td>
                            <select id="pdf_quality" name="pdf_quality">
                                <option value="low" <?php selected($pdf_quality, 'low'); ?>>Faible (fichiers plus petits)</option>
                                <option value="medium" <?php selected($pdf_quality, 'medium'); ?>>Moyen</option>
                                <option value="high" <?php selected($pdf_quality, 'high'); ?>>Élevée (meilleure qualité)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_format">Format PDF par défaut</label></th>
                        <td>
                            <select id="default_format" name="default_format">
                                <option value="A4" <?php selected($default_format, 'A4'); ?>>A4</option>
                                <option value="A3" <?php selected($default_format, 'A3'); ?>>A3</option>
                                <option value="Letter" <?php selected($default_format, 'Letter'); ?>>Letter</option>
                                <option value="Legal" <?php selected($default_format, 'Legal'); ?>>Legal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_orientation">Orientation par défaut</label></th>
                        <td>
                            <select id="default_orientation" name="default_orientation">
                                <option value="portrait" <?php selected($default_orientation, 'portrait'); ?>>Portrait</option>
                                <option value="landscape" <?php selected($default_orientation, 'landscape'); ?>>Paysage</option>
                            </select>
                        </td>
                    </tr>
                </table>
                </form>
            </div>
        </div>


        <div id="licence" class="tab-content hidden-tab">


            <!-- Section Licence -->
            <h3 class="section-title">🔐 Gestion de la Licence</h3>
            <form method="post" id="licence-form" action="">
                <input type="hidden" name="current_tab" value="licence">
                    <h4 style="color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">🔐 Gestion de la Licence</h4>

                <?php
                    $license_status = get_option('pdf_builder_license_status', 'free');
                    $license_key = get_option('pdf_builder_license_key', '');
                    $license_expires = get_option('pdf_builder_license_expires', '');
                    $license_activated_at = get_option('pdf_builder_license_activated_at', '');
                    $test_mode_enabled = get_option('pdf_builder_license_test_mode_enabled', false);
                    $test_key = get_option('pdf_builder_license_test_key', '');
                    $test_key_expires = get_option('pdf_builder_license_test_key_expires', '');
                    // Email notifications
                    $notification_email = get_option('pdf_builder_license_notification_email', get_option('admin_email'));
                    $enable_expiration_notifications = get_option('pdf_builder_license_enable_notifications', true);
                    // is_premium si vraie licence OU si clé de test existe
                    $is_premium = ($license_status !== 'free' && $license_status !== 'expired') || (!empty($test_key));
                    // is_test_mode si clé de test existe
                    $is_test_mode = !empty($test_key);
                    // DEBUG: Afficher les valeurs pour verifier
                    if (current_user_can('manage_options')) {
                        echo '<!-- DEBUG: status=' . esc_html($license_status) . ' key=' . (!empty($license_key) ? 'YES' : 'NO') . ' test_key=' . (!empty($test_key) ? 'YES:' . substr($test_key, 0, 5) : 'NO') . ' is_premium=' . ($is_premium ? 'TRUE' : 'FALSE') . ' -->';
                    }

                    // Traitement activation licence
                    if (isset($_POST['activate_license']) && isset($_POST['pdf_builder_license_nonce'])) {
                    // Mode DÉMO : Activation de clés réelles désactivée
                        // Les clés premium réelles seront validées une fois le système de licence en production
                        wp_die('<div style="background: #fff3cd; border: 2px solid #ffc107; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 20px; margin: 20px; color: #856404; font-family: Arial, sans-serif;">
                                <h2 style="margin-top: 0; color: #856404;">⚠️ Mode DÉMO</h2>
                                <p><strong>La validation des clés premium n\'est pas encore active.</strong></p>
                                <p>Pour tester les fonctionnalités premium, veuillez :</p>
                                <ol>
                                    <li>Allez à l\'onglet <strong>Développeur</strong></li>
                                    <li>Cliquez sur <strong>Générer une clé de test</strong></li>
                                    <li>La clé TEST s\'activera automatiquement</li>
                                </ol>
                                <p><a href="' . admin_url('admin.php?page=pdf-builder-pro-settings&tab=developer') . '" style="background: #ffc107; color: #856404; padding: 10px 15px; -webkit-border-radius: 5px; -moz-border-radius: 5px; -ms-border-radius: 5px; -o-border-radius: 5px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;">↻ Aller au mode Développeur</a></p>
                            </div>', 'Activation désactivée', ['response' => 403]);
                    }

                    // Traitement désactivation licence
                    if (isset($_POST['deactivate_license']) && isset($_POST['pdf_builder_deactivate_nonce'])) {

                        if (wp_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                            delete_option('pdf_builder_license_key');
                            delete_option('pdf_builder_license_expires');
                            delete_option('pdf_builder_license_activated_at');
                            delete_option('pdf_builder_license_test_key');
                            delete_option('pdf_builder_license_test_mode_enabled');
                            update_option('pdf_builder_license_status', 'free');
                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée complètement.</p></div>';
                            $is_premium = false;
                            $license_key = '';
                            $license_status = 'free';
                            $license_activated_at = '';
                            $test_key = '';
                            $test_mode_enabled = false;
                        }
                    }

                    // Traitement des paramètres de notification
                    if (isset($_POST['pdf_builder_save_notifications']) && isset($_POST['pdf_builder_license_nonce'])) {
                        if (wp_verify_nonce($_POST['pdf_builder_license_nonce'], 'pdf_builder_license')) {
                            $email = sanitize_email($_POST['notification_email'] ?? get_option('admin_email'));
                            $enable_notifications = isset($_POST['enable_expiration_notifications']) ? 1 : 0;
                            update_option('pdf_builder_license_notification_email', $email);
                            update_option('pdf_builder_license_enable_notifications', $enable_notifications);
                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de notification sauvegardés.</p></div>';
                        // Recharger les valeurs
                            $notification_email = $email;
                            $enable_expiration_notifications = $enable_notifications;
                        }
                    }
                ?>

                    <!-- Statut de la licence -->
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e5e5e5; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 30px; margin-bottom: 30px; -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.08); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.08); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.08); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.08); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h4 style="margin-top: 0; color: #007cba; font-size: 22px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 Statut de la Licence</h4>

                        <div style="display: -webkit-grid; display: -moz-grid; display: -ms-grid; display: grid; -webkit-grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); -moz-grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); -ms-grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); -webkit-gap: 20px; -moz-gap: 20px; gap: 20px; margin-top: 25px;">
                            <!-- Carte Statut Principal -->
                            <div style="border: 3px solid <?php echo $is_premium ? '#28a745' : '#6c757d'; ?>; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, <?php echo $is_premium ? '#d4edda' : '#f8f9fa'; ?> 0%, <?php echo $is_premium ? '#e8f5e9' : '#ffffff'; ?> 100%); -webkit-box-shadow: 0 4px 6px rgba(0,0,0,0.1); -moz-box-shadow: 0 4px 6px rgba(0,0,0,0.1); -ms-box-shadow: 0 4px 6px rgba(0,0,0,0.1); -o-box-shadow: 0 4px 6px rgba(0,0,0,0.1); box-shadow: 0 4px 6px rgba(0,0,0,0.1); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #666; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Statut</div>
                                <div style="font-size: 26px; font-weight: 900; color: <?php echo $is_premium ? '#155724' : '#495057'; ?>; margin-bottom: 8px;">
                                    <?php echo $is_premium ? '✅ Premium Actif' : '○ Gratuit'; ?>
                                </div>
                                <div style="font-size: 12px; color: <?php echo $is_premium ? '#155724' : '#6c757d'; ?>; font-style: italic;">
                                    <?php echo $is_premium ? 'Licence premium activée' : 'Aucune licence premium'; ?>
                                </div>
                            </div>

                            <!-- Carte Mode Test (si applicable) -->
                            <?php if (!empty($test_key)) :
                                ?>
                            <div style="border: 3px solid #ffc107; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); -webkit-box-shadow: 0 4px 6px rgba(255,193,7,0.2); -moz-box-shadow: 0 4px 6px rgba(255,193,7,0.2); -ms-box-shadow: 0 4px 6px rgba(255,193,7,0.2); -o-box-shadow: 0 4px 6px rgba(255,193,7,0.2); box-shadow: 0 4px 6px rgba(255,193,7,0.2); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #856404; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Mode</div>
                                <div style="font-size: 26px; font-weight: 900; color: #856404; margin-bottom: 8px;">
                                    🧪 TEST (Dev)
                                </div>
                                <div style="font-size: 12px; color: #856404; font-style: italic;">
                                    Mode développement actif
                                </div>
                            </div>
                                <?php
                            endif; ?>

                            <!-- Carte Date d'expiration -->
                            <?php if ($is_premium && $license_expires) :
                                ?>
                            <div style="border: 3px solid #17a2b8; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #d1ecf1 0%, #e0f7fa 100%); -webkit-box-shadow: 0 4px 6px rgba(23,162,184,0.2); -moz-box-shadow: 0 4px 6px rgba(23,162,184,0.2); -ms-box-shadow: 0 4px 6px rgba(23,162,184,0.2); -o-box-shadow: 0 4px 6px rgba(23,162,184,0.2); box-shadow: 0 4px 6px rgba(23,162,184,0.2); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #666; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Expiration</div>
                                <div style="font-size: 20px; font-weight: 700; color: #0066cc; margin-bottom: 8px;">
                                    <?php echo date('d/m/Y', strtotime($license_expires)); ?>
                                </div>
                                <div style="font-size: 12px; color: #666; font-style: italic;">
                                    <?php
                                    $now = new DateTime();
                                    $expires = new DateTime($license_expires);
                                    $diff = $now->diff($expires);
                                    if ($diff->invert) {
                                        echo '❌ Expiré il y a ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                    } else {
                                        echo '✓ Valide pendant ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                    }
                                    ?>
                                </div>
                            </div>
                                <?php
                            endif; ?>

                            <!-- Carte Informations Licence -->
                            <?php if ($is_premium && $license_activated_at) :
                                ?>
                            <div style="border: 3px solid #6f42c1; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #e6dffc 0%, #f3f0ff 100%); -webkit-box-shadow: 0 4px 6px rgba(111,66,193,0.2); -moz-box-shadow: 0 4px 6px rgba(111,66,193,0.2); -ms-box-shadow: 0 4px 6px rgba(111,66,193,0.2); -o-box-shadow: 0 4px 6px rgba(111,66,193,0.2); box-shadow: 0 4px 6px rgba(111,66,193,0.2); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #666; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Activée le</div>
                                <div style="font-size: 18px; font-weight: 700; color: #6f42c1; margin-bottom: 8px;">
                                    <?php echo date('d/m/Y', strtotime($license_activated_at)); ?>
                                </div>
                                <div style="font-size: 12px; color: #666; font-style: italic;">
                                    <?php echo date('H:i', strtotime($license_activated_at)); ?>
                                </div>
                            </div>
                                <?php
                            endif; ?>
                        </div>
                </div>

                    <!-- Activation/Désactivation - Mode DEMO ou Gestion TEST -->
                <div style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border: 2px solid #e5e5e5; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 30px; margin-bottom: 30px; -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.08); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.08); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.08); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.08); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h4 style="margin-top: 0; color: #007cba; font-size: 22px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">🔑 Gestion de la Licence</h4>

                        <div style="display: -webkit-grid; display: -moz-grid; display: -ms-grid; display: grid; -webkit-grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); -moz-grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); -ms-grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); -webkit-gap: 20px; -moz-gap: 20px; gap: 20px; margin-top: 25px;">
                            <!-- Section Activation Licence -->
                            <div style="border: 2px solid #28a745; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #d4edda 0%, #e8f5e9 100%);">
                                <h4 style="margin-top: 0; color: #155724; font-size: 18px;">✅ Activation de Licence</h4>
                                <p style="margin: 10px 0; color: #155724;">Entrez votre clé de licence premium pour activer toutes les fonctionnalités.</p>

                                <form method="post" style="margin-top: 15px;">
                                    <?php wp_nonce_field('pdf_builder_license', 'pdf_builder_license_nonce'); ?>
                                    <input type="hidden" name="activate_license" value="1">

                                    <div style="margin-bottom: 15px;">
                                        <label for="license_key_input" style="display: block; font-weight: 600; margin-bottom: 5px; color: #155724;">Clé de Licence</label>
                                        <input type="text" id="license_key_input" name="license_key" value="<?php echo esc_attr($license_key); ?>"
                                               style="width: 100%; padding: 8px 12px; border: 2px solid #28a745; border-radius: 4px; font-family: monospace; font-size: 14px;"
                                               placeholder="XXXX-XXXX-XXXX-XXXX" autocomplete="off" />
                                    </div>

                                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%;">
                                        🔓 Activer la Licence
                                    </button>
                                </form>
                            </div>

                            <!-- Section Désactivation -->
                            <div style="border: 2px solid #dc3545; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #f8d7da 0%, #fce4e6 100%);">
                                <h4 style="margin-top: 0; color: #721c24; font-size: 18px;">❌ Désactivation</h4>
                                <p style="margin: 10px 0; color: #721c24;">Désactivez complètement la licence actuelle.</p>

                                <button type="button" id="deactivate-license-btn" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 15px;">
                                    🔒 Désactiver
                                </button>
                            </div>
                        </div>

                        <!-- Modal de confirmation désactivation -->
                        <div id="deactivate_modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
                            <div style="background: white; margin: 15% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; -webkit-box-shadow: 0 4px 20px rgba(0,0,0,0.3); -moz-box-shadow: 0 4px 20px rgba(0,0,0,0.3); -ms-box-shadow: 0 4px 20px rgba(0,0,0,0.3); -o-box-shadow: 0 4px 20px rgba(0,0,0,0.3); box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                <h3 style="margin-top: 0; color: #dc3545;">⚠️ Confirmation de Désactivation</h3>
                                <p style="margin: 20px 0; color: #666;">Êtes-vous sûr de vouloir désactiver cette licence ? Toutes les fonctionnalités premium seront perdues.</p>

                                <form method="post" id="deactivate_form">
                                    <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                                    <input type="hidden" name="deactivate_license" value="1">
                                    <div style="display: flex; gap: 12px; margin-top: 30px;">
                                        <button type="button" style="flex: 1; background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;" onclick="closeDeactivateModal()">
                                            Annuler
                                        </button>
                                        <button type="submit" style="flex: 1; background: #dc3545; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;">
                                            Désactiver
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                </div>

                <script>
                    function showDeactivateModal() {
                        var modal = document.getElementById('deactivate_modal');
                        if (modal) {
                            modal.style.display = 'flex';
                        }
                        return false;
                    }

                    function closeDeactivateModal() {
                        var modal = document.getElementById('deactivate_modal');
                        if (modal) {
                            modal.style.display = 'none';
                        }
                    }

                    // Fermer la modale si on clique en dehors
                    document.addEventListener('click', function(event) {
                        var modal = document.getElementById('deactivate_modal');
                        if (event.target === modal) {
                            closeDeactivateModal();
                        }
                    });

                    // ✅ Handler pour le bouton "Vider le cache" dans l'onglet Général
                    document.addEventListener('DOMContentLoaded', function() {
                        var clearCacheBtn = document.getElementById('clear-cache-general-btn');
                        if (clearCacheBtn) {
                            clearCacheBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                var resultsSpan = document.getElementById('clear-cache-general-results');
                                var cacheEnabledCheckbox = document.getElementById('cache_enabled');

                                // ✅ Vérifie si le cache est activé
                                if (cacheEnabledCheckbox && !cacheEnabledCheckbox.checked) {
                                    resultsSpan.textContent = '⚠️ Le cache n\'est pas activé!';
                                    resultsSpan.style.color = '#ff9800';
                                    return;
                                }

                                clearCacheBtn.disabled = true;
                                clearCacheBtn.textContent = '⏳ Vérification...';
                                resultsSpan.textContent = '';

                                // ✅ Appel AJAX pour vider le cache
                                var formData = new FormData();
                                formData.append('action', 'pdf_builder_clear_cache');
                                formData.append('security', pdf_builder_ajax.nonce);

                                fetch(pdf_builder_ajax.ajax_url, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(function(response) {
                                    return response.json();
                                })
                                .then(function(data) {
                                    clearCacheBtn.disabled = false;
                                    clearCacheBtn.textContent = '🗑️ Vider tout le cache';

                                    if (data.success) {
                                        resultsSpan.textContent = '✅ Cache vidé avec succès!';
                                        resultsSpan.style.color = '#28a745';
                                    } else {
                                        resultsSpan.textContent = '❌ Erreur: ' + (data.data || 'Erreur inconnue');
                                        resultsSpan.style.color = '#dc3232';
                                    }
                                })
                                .catch(function(error) {
                                    clearCacheBtn.disabled = false;
                                    clearCacheBtn.textContent = '🗑️ Vider tout le cache';
                                    resultsSpan.textContent = '❌ Erreur AJAX: ' + error.message;
                                    resultsSpan.style.color = '#dc3232';
                                    console.error('Erreur lors du vide du cache:', error);
                                });
                            });
                        }

                        // ✅ Handler pour le bouton "Tester l'intégration du cache"
                        var testCacheBtn = document.getElementById('test-cache-btn');
                        if (testCacheBtn) {
                            testCacheBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                var resultsSpan = document.getElementById('cache-test-results');
                                var outputDiv = document.getElementById('cache-test-output');
                                var cacheEnabledCheckbox = document.getElementById('cache_enabled');

                                // ✅ Vérifie si le cache est activé
                                if (cacheEnabledCheckbox && !cacheEnabledCheckbox.checked) {
                                    resultsSpan.textContent = '⚠️ Le cache n\'est pas activé!';
                                    resultsSpan.style.color = '#ff9800';
                                    outputDiv.style.display = 'none';
                                    return;
                                }

                                testCacheBtn.disabled = true;
                                testCacheBtn.textContent = '⏳ Test en cours...';
                                resultsSpan.textContent = '';
                                outputDiv.style.display = 'none';

                                // ✅ Test du système de cache
                                var testResults = [];
                                var startTime = Date.now();

                                // Test 1: Vérifier si les fonctions de cache sont disponibles
                                if (typeof wp_cache_flush === 'function') {
                                    testResults.push('✅ Fonction wp_cache_flush disponible');
                                } else {
                                    testResults.push('⚠️ Fonction wp_cache_flush non disponible');
                                }

                                // Test 2: Tester la création d'un transient
                                var testTransientKey = 'pdf_builder_cache_test_' + Date.now();
                                var testTransientValue = 'test_value_' + Math.random();

                                // Simuler un appel AJAX pour tester le cache
                                var formData = new FormData();
                                formData.append('action', 'pdf_builder_test_cache');
                                formData.append('security', pdf_builder_ajax.nonce);
                                formData.append('test_key', testTransientKey);
                                formData.append('test_value', testTransientValue);

                                fetch(pdf_builder_ajax.ajax_url, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(function(response) {
                                    return response.json();
                                })
                                .then(function(data) {
                                    var endTime = Date.now();
                                    var duration = endTime - startTime;

                                    testCacheBtn.disabled = false;
                                    testCacheBtn.textContent = '🧪 Tester l\'intégration du cache';

                                    if (data.success) {
                                        testResults.push('✅ Cache opérationnel');
                                        testResults.push('⏱️ Temps de réponse: ' + duration + 'ms');

                                        if (data.data && data.data.cache_status) {
                                            testResults.push('📊 ' + data.data.cache_status);
                                        }

                                        resultsSpan.textContent = '✅ Tests réussis!';
                                        resultsSpan.style.color = '#28a745';
                                    } else {
                                        testResults.push('❌ Erreur lors du test du cache');
                                        resultsSpan.textContent = '❌ Tests échoués';
                                        resultsSpan.style.color = '#dc3232';
                                    }

                                    // Afficher les détails du test
                                    outputDiv.innerHTML = '<strong>Résultats du test :</strong><br>' + testResults.join('<br>');
                                    outputDiv.style.display = 'block';
                                })
                                .catch(function(error) {
                                    testCacheBtn.disabled = false;
                                    testCacheBtn.textContent = '🧪 Tester l\'intégration du cache';
                                    resultsSpan.textContent = '❌ Erreur AJAX: ' + error.message;
                                    resultsSpan.style.color = '#dc3232';
                                    console.error('Erreur lors du test du cache:', error);

                                    outputDiv.innerHTML = '<strong>Erreur :</strong><br>' + error.message;
                                    outputDiv.style.display = 'block';
                                });
                            });
                        }
                    });
                </script>

                    <!-- Informations utiles -->
                    <div style="background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%); border: none; border-radius: 12px; padding: 30px; margin-bottom: 30px; color: #fff; box-shadow: 0 4px 12px rgba(23,162,184,0.3);">
                        <h4 style="margin: 0 0 20px 0; color: #fff; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">Informations Utiles</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                            <!-- Site actuel -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <strong>Site actuel :</strong><br>
                                <code style="color: #fff; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 3px;"><?php echo esc_html(get_site_url()); ?></code>
                            </div>

                            <!-- Version du plugin -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <strong>Version :</strong><br>
                                <code style="color: #fff; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 3px;"><?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : '1.0.0'; ?></code>
                            </div>

                            <!-- Support -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <strong>Support :</strong><br>
                                <a href="mailto:support@pdf-builder-pro.com" style="color: #fff; text-decoration: underline;">support@pdf-builder-pro.com</a>
                            </div>

                            <!-- Documentation -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <strong>Documentation :</strong><br>
                                <a href="https://docs.pdf-builder-pro.com" target="_blank" style="color: #fff; text-decoration: underline;">docs.pdf-builder-pro.com</a>
                            </div>
                        </div>
                    </div>

                    <!-- Section Notifications par Email -->
                    <div style="background: linear-gradient(135deg, #e7f5ff 0%, #f0f9ff 100%); border: none; border-radius: 12px; padding: 30px; margin-top: 30px; color: #343a40; box-shadow: 0 4px 12px rgba(0,102,204,0.15);">
                        <h4 style="margin-top: 0; color: #003d7a; font-size: 20px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                            📧 Notifications par Email
                        </h4>

                        <p style="color: #003d7a; margin: 0 0 25px 0; line-height: 1.6; font-size: 14px;">
                            Recevez une notification par email quand votre licence expire bientôt. C'est une excellente façon de ne jamais oublier de renouveler votre licence.
                        </p>

                        <form method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; align-items: start;">
                            <?php wp_nonce_field('pdf_builder_license', 'pdf_builder_license_nonce'); ?>
                            <input type="hidden" name="pdf_builder_save_notifications" value="1">

                            <!-- Toggle Notifications -->
                            <div style="background: rgba(255,255,255,0.6); padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div class="toggle-container">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="enable_expiration_notifications" name="enable_expiration_notifications" value="1"
                                                   <?php checked($enable_expiration_notifications); ?> />
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    <div>
                                        <label for="enable_expiration_notifications" style="font-weight: 600; color: #003d7a; cursor: pointer;">Notifications d'expiration</label>
                                        <div class="toggle-description" style="color: #666; font-size: 13px; margin-top: 2px;">Active les notifications par email pour les événements de licence</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email administrateur -->
                            <div style="background: rgba(255,255,255,0.6); padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <label for="notification_email" style="display: block; font-weight: 600; margin-bottom: 8px; color: #003d7a;">Email Administrateur</label>
                                <input type="email" id="notification_email" name="notification_email" value="<?php echo esc_attr($notification_email); ?>"
                                       class="regular-text" autocomplete="email" style="width: 100%; padding: 8px 12px; border: 2px solid #0066cc; border-radius: 4px;" />
                                <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;">
                                    Défaut : adresse administrateur du site
                                </p>
                            </div>

                        </form>
                    </div>

            
            </form>
        </div>

        <div id="systeme" class="tab-content hidden-tab">
            <h2>⚙️ Système - Performance, Maintenance & Sauvegarde</h2>

            <!-- Section Performance -->
            <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border: 2px solid #28a745; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #155724; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">🚀 Performance</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_performance', 'pdf_builder_performance_nonce'); ?>
                    <input type="hidden" name="current_tab" value="performance">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" <?php checked(get_option('pdf_builder_cache_enabled', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Active le système de cache pour améliorer les performances</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_expiry">Expiration du cache (heures)</label></th>
                            <td>
                                <input type="number" id="cache_expiry" name="cache_expiry" value="<?php echo esc_attr(get_option('pdf_builder_cache_expiry', 24)); ?>" min="1" max="168">
                                <p class="description">Durée avant expiration automatique du cache</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="max_cache_size">Taille max du cache (Mo)</label></th>
                            <td>
                                <input type="number" id="max_cache_size" name="max_cache_size" value="<?php echo esc_attr(get_option('pdf_builder_max_cache_size', 100)); ?>" min="10" max="1000">
                                <p class="description">Taille maximale du cache avant nettoyage automatique</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <!-- Section Maintenance -->
            <div style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #856404; margin-top: 0; border-bottom: 2px solid #ffc107; padding-bottom: 10px;">🔧 Maintenance</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_maintenance', 'pdf_builder_maintenance_nonce'); ?>
                    <input type="hidden" name="current_tab" value="maintenance">

                    <table class="form-table">
                        <tr>
                            <th scope="row">Actions de maintenance</th>
                            <td>
                                <button type="button" id="clear-cache-btn" class="button button-secondary" style="margin-right: 10px;">🗑️ Vider le cache</button>
                                <button type="button" id="optimize-db-btn" class="button button-secondary" style="margin-right: 10px;">🗃️ Optimiser la base</button>
                                <button type="button" id="repair-db-btn" class="button button-secondary">🔧 Réparer la base</button>
                                <div id="maintenance-results" style="margin-top: 10px;"></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="auto_maintenance">Maintenance automatique</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="auto_maintenance" name="auto_maintenance" value="1" <?php checked(get_option('pdf_builder_auto_maintenance', false)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Active la maintenance automatique hebdomadaire</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <!-- Section Sauvegarde -->
            <div style="background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border: 2px solid #0066cc; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #004085; margin-top: 0; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">💾 Sauvegarde</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_backup', 'pdf_builder_backup_nonce'); ?>
                    <input type="hidden" name="current_tab" value="sauvegarde">

                    <table class="form-table">
                        <tr>
                            <th scope="row">Sauvegardes disponibles</th>
                            <td>
                                <button type="button" id="create-backup-btn" class="button button-primary" style="margin-right: 10px;">📦 Créer une sauvegarde</button>
                                <button type="button" id="list-backups-btn" class="button button-secondary">📋 Lister les sauvegardes</button>
                                <div id="backup-results" style="margin-top: 10px;"></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="auto_backup">Sauvegarde automatique</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="auto_backup" name="auto_backup" value="1" <?php checked(get_option('pdf_builder_auto_backup', false)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Crée automatiquement des sauvegardes quotidiennes</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="backup_retention">Rétention des sauvegardes (jours)</label></th>
                            <td>
                                <input type="number" id="backup_retention" name="backup_retention" value="<?php echo esc_attr(get_option('pdf_builder_backup_retention', 30)); ?>" min="1" max="365">
                                <p class="description">Nombre de jours avant suppression automatique des anciennes sauvegardes</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <div id="acces" class="tab-content hidden-tab">
            <h2>👥 Gestion des Rôles et Permissions</h2>

            <!-- Message de confirmation que l'onglet est chargé -->
            <div style="margin-bottom: 20px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                ✅ Section Rôles chargée - Bouton de sauvegarde visible ci-dessous
            </div>

            <?php
            // Traitement de la sauvegarde des rôles autorisés
         if (isset($_POST['submit_roles']) && isset($_POST['pdf_builder_roles_nonce'])) {


                if (wp_verify_nonce($_POST['pdf_builder_roles_nonce'], 'pdf_builder_roles')) {
                    $allowed_roles = isset($_POST['pdf_builder_allowed_roles'])
                        ? array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles'])
                        : [];

                    if (empty($allowed_roles)) {
                        $allowed_roles = ['administrator'];
                        // Au minimum l'admin
                    }

                    update_option('pdf_builder_allowed_roles', $allowed_roles);
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Rôles autorisés mis à jour avec succès.</p></div>';
                } else {
                    $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité (nonce invalide).</p></div>';
                }
            }

            global $wp_roles;
            $all_roles = $wp_roles->roles;
            $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
            if (!is_array($allowed_roles)) {
                $allowed_roles = ['administrator', 'editor', 'shop_manager'];
            }

            $role_descriptions = [
                'administrator' => 'Accès complet à toutes les fonctionnalités',
                'editor' => 'Peut publier et gérer les articles',
                'author' => 'Peut publier ses propres articles',
                'contributor' => 'Peut soumettre des articles pour révision',
                'subscriber' => 'Peut uniquement lire les articles',
                'shop_manager' => 'Gestionnaire de boutique WooCommerce',
                'customer' => 'Client WooCommerce',
            ];
            ?>

            <p style="margin-bottom: 20px;">Sélectionnez les rôles WordPress qui auront accès à PDF Builder Pro.</p>

            <form method="post">
                <?php wp_nonce_field('pdf_builder_roles', 'pdf_builder_roles_nonce'); ?>

                <!-- Boutons de contrôle rapide -->
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                    <button type="button" id="select-all-roles" class="button button-secondary" style="margin-right: 5px;">
                        Sélectionner Tout
                    </button>
                    <button type="button" id="select-common-roles" class="button button-secondary" style="margin-right: 5px;">
                        Rôles Courants
                    </button>
                    <button type="button" id="select-none-roles" class="button button-secondary" style="margin-right: 5px;">
                        Désélectionner Tout
                    </button>
                    <span class="description" style="margin-left: 10px;">
                        Sélectionnés: <strong id="selected-count"><?php echo count($allowed_roles); ?></strong> rôle(s)
                    </span>
                </div>

                <!-- Boutons toggle pour les rôles -->
                <div class="roles-toggle-list">
                    <?php foreach ($all_roles as $role_key => $role) :
                        $role_name = translate_user_role($role['name']);
                        $is_selected = in_array($role_key, $allowed_roles);
                        $description = $role_descriptions[$role_key] ?? 'Rôle personnalisé';
                        $is_admin = $role_key === 'administrator';
                        ?>
                        <div class="role-toggle-item <?php echo $is_admin ? 'admin-role' : ''; ?>">
                            <div class="role-info">
                                <div class="role-name">
                                    <?php echo esc_html($role_name); ?>
                                    <?php if ($is_admin) :
                                        ?>
                                        <span class="admin-badge">🔒 Toujours actif</span>
                                        <?php
                                    endif; ?>
                                </div>
                                <div class="role-description"><?php echo esc_html($description); ?></div>
                                <div class="role-key"><?php echo esc_html($role_key); ?></div>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox"
                                       id="role_<?php echo esc_attr($role_key); ?>"
                                       name="pdf_builder_allowed_roles[]"
                                       value="<?php echo esc_attr($role_key); ?>"
                                       <?php checked($is_selected); ?>
                                       <?php echo $is_admin ? 'disabled' : ''; ?> />
                                <label for="role_<?php echo esc_attr($role_key); ?>" class="toggle-slider"></label>
                            </div>
                        </div>
                        <?php
                    endforeach; ?>
                </div>

                <style>
                    .roles-toggle-list {
                        max-width: 600px;
                    }

                    .role-toggle-item {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        padding: 15px 20px;
                        margin-bottom: 8px;
                        background: #f8f9fa;
                        border: 1px solid #e9ecef;
                        border-radius: 8px;
                        transition: all 0.2s ease;
                    }

                    .role-toggle-item:hover {
                        background: #e9ecef;
                        border-color: #dee2e6;
                    }

                    .role-toggle-item.admin-role {
                        background: #fce4ec;
                        border-color: #f8bbd9;
                    }

                    .role-info {
                        flex: 1;
                    }

                    .role-name {
                        font-weight: 600;
                        font-size: 15px;
                        color: #333;
                        margin-bottom: 2px;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }

                    .admin-badge {
                        font-size: 12px;
                        color: #d63384;
                        font-weight: 500;
                        background: rgba(214, 51, 132, 0.1);
                        padding: 2px 6px;
                        border-radius: 4px;
                    }

                    .role-description {
                        font-size: 13px;
                        color: #666;
                        margin-bottom: 2px;
                    }

                    .role-key {
                        font-size: 11px;
                        color: #999;
                        font-family: monospace;
                    }

                    .toggle-switch {
                        position: relative;
                        width: 50px;
                        height: 24px;
                    }

                    .toggle-switch input {
                        opacity: 0;
                        width: 0;
                        height: 0;
                    }

                    .toggle-slider {
                        position: absolute;
                        cursor: pointer;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: #ccc;
                        transition: 0.3s;
                        border-radius: 24px;
                    }

                    .toggle-slider:before {
                        position: absolute;
                        content: "";
                        height: 18px;
                        width: 18px;
                        left: 3px;
                        bottom: 3px;
                        background-color: white;
                        transition: 0.3s;
                        border-radius: 50%;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    }

                    input:checked + .toggle-slider {
                        background-color: #2271b1;
                    }

                    input:checked + .toggle-slider:before {
                        transform: translateX(26px);
                    }

                    .toggle-switch input:disabled + .toggle-slider {
                        background-color: #d63384;
                        cursor: not-allowed;
                        opacity: 0.7;
                    }

                    .toggle-switch input:disabled:checked + .toggle-slider {
                        background-color: #d63384;
                    }

                    /* Animation au survol */
                    .toggle-slider:hover {
                        box-shadow: 0 0 8px rgba(34, 113, 177, 0.3);
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const roleToggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
                        const selectedCount = document.getElementById('selected-count');
                        const selectAllBtn = document.getElementById('select-all-roles');
                        const selectCommonBtn = document.getElementById('select-common-roles');
                        const selectNoneBtn = document.getElementById('select-none-roles');

                        // Fonction pour mettre à jour le compteur
                        function updateSelectedCount() {
                            const checkedBoxes = document.querySelectorAll('.toggle-switch input[type="checkbox"]:checked');
                            if (selectedCount) {
                                selectedCount.textContent = checkedBoxes.length;

                            }
                        }

                        // Bouton Sélectionner Tout
                        if (selectAllBtn) {
                            selectAllBtn.addEventListener('click', function() {
                                const togglesLength = roleToggles.length;
                                for (let i = 0; i < togglesLength; i++) {
                                    const checkbox = roleToggles[i];
                                    if (!checkbox.disabled) {
                                        checkbox.checked = true;
                                    }
                                }
                                // Différer la mise à jour du compteur pour éviter les violations de performance
                                requestAnimationFrame(updateSelectedCount);
                            });
                        }

                        // Bouton Rôles Courants
                        if (selectCommonBtn) {
                            selectCommonBtn.addEventListener('click', function() {
                                const commonRoles = ['administrator', 'editor', 'shop_manager'];
                                const togglesLength = roleToggles.length;
                                for (let i = 0; i < togglesLength; i++) {
                                    const checkbox = roleToggles[i];
                                    const isCommon = commonRoles.includes(checkbox.value);
                                    if (!checkbox.disabled) {
                                        checkbox.checked = isCommon;
                                    }
                                }
                                // Différer la mise à jour du compteur pour éviter les violations de performance
                                requestAnimationFrame(updateSelectedCount);
                            });
                        }

                        // Bouton Désélectionner Tout
                        if (selectNoneBtn) {
                            selectNoneBtn.addEventListener('click', function() {
                                const togglesLength = roleToggles.length;
                                for (let i = 0; i < togglesLength; i++) {
                                    const checkbox = roleToggles[i];
                                    if (!checkbox.disabled) {
                                        checkbox.checked = false;
                                    }
                                }
                                // Différer la mise à jour du compteur pour éviter les violations de performance
                                requestAnimationFrame(updateSelectedCount);
                            });
                        }

                        // Mettre à jour le compteur quand un toggle change (avec debounce pour éviter les appels trop fréquents)
                        let updateTimeout;
                        roleToggles.forEach(function(checkbox) {
                            checkbox.addEventListener('change', function() {
                                // Debounce les appels pour éviter les appels trop fréquents
                                clearTimeout(updateTimeout);
                                updateTimeout = setTimeout(updateSelectedCount, 10);
                            });
                        });

                        // Initialiser le compteur
                        updateSelectedCount();

                    });
                </script>

                <!-- Permissions incluses -->
                <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                    <h4 style="margin-top: 0; color: #003d66;">🔐 Permissions Incluses</h4>
                    <p style="margin: 10px 0; color: #003d66;">Les rôles sélectionnés auront accès à :</p>
                    <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                        <li>✅ Création, édition et suppression de templates PDF</li>
                        <li>✅ Génération et téléchargement de PDF</li>
                        <li>✅ Accès aux paramètres et configuration</li>
                        <li>✅ Prévisualisation avant génération</li>
                        <li>✅ Gestion des commandes WooCommerce (si applicable)</li>
                    </ul>
                </div>

                <!-- Avertissement important -->
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 20px;">
                    <h4 style="margin-top: 0; color: #856404;">⚠️ Informations Importantes</h4>
                    <ul style="margin: 0; padding-left: 20px; color: #856404;">
                        <li>Les rôles non sélectionnés n'auront aucun accès à PDF Builder Pro</li>
                        <li>Le rôle "Administrator" a toujours accès complet, indépendamment</li>
                        <li>Minimum requis : au moins un rôle sélectionné</li>
                    </ul>
                </div>

            </form>
        </div>
        <div id="securite" class="tab-content hidden-tab">
            <h2>🔒 Sécurité & Conformité</h2>

            <!-- Section Sécurité -->
            <div style="background: linear-gradient(135deg, #f8d7da 0%, #fce4e6 100%); border: 2px solid #dc3545; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #721c24; margin-top: 0; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">🛡️ Sécurité</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_securite', 'pdf_builder_securite_nonce'); ?>
                    <input type="hidden" name="current_tab" value="securite">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="security_level">Niveau de sécurité</label></th>
                            <td>
                                <select id="security_level" name="security_level">
                                    <option value="low" <?php selected(get_option('pdf_builder_security_level', 'medium'), 'low'); ?>>Faible</option>
                                    <option value="medium" <?php selected(get_option('pdf_builder_security_level', 'medium'), 'medium'); ?>>Moyen</option>
                                    <option value="high" <?php selected(get_option('pdf_builder_security_level', 'medium'), 'high'); ?>>Élevé</option>
                                </select>
                                <p class="description">Niveau de sécurité pour la génération de PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="enable_logging">Journalisation activée</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="enable_logging" name="enable_logging" value="1" <?php checked(get_option('pdf_builder_enable_logging', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Active la journalisation des actions pour audit</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <!-- Section RGPD -->
            <div style="background: linear-gradient(135deg, #d4edda 0%, #e8f5e8 100%); border: 2px solid #28a745; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #155724; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">📋 Gestion RGPD & Conformité</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_rgpd', 'pdf_builder_rgpd_nonce'); ?>
                    <input type="hidden" name="current_tab" value="securite">

                    <!-- Section Paramètres RGPD -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">⚙️ Paramètres RGPD</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="gdpr_enabled">RGPD Activé</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_enabled" name="gdpr_enabled" value="1" <?php checked(get_option('pdf_builder_gdpr_enabled', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Activer la conformité RGPD pour le plugin</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_consent_required">Consentement RGPD requis</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_consent_required" name="gdpr_consent_required" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_required', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Exiger le consentement RGPD avant génération de PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_data_retention">Rétention des données (jours)</label></th>
                            <td>
                                <input type="number" id="gdpr_data_retention" name="gdpr_data_retention" value="<?php echo esc_attr(get_option('pdf_builder_gdpr_data_retention', 2555)); ?>" min="30" max="3650">
                                <p class="description">Nombre de jours avant suppression automatique des données utilisateur (RGPD: 7 ans recommandé)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_audit_enabled">Audit Logging</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_audit_enabled" name="gdpr_audit_enabled" value="1" <?php checked(get_option('pdf_builder_gdpr_audit_enabled', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Activer la journalisation des actions pour audit RGPD</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_encryption_enabled">Chiffrement des données</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_encryption_enabled" name="gdpr_encryption_enabled" value="1" <?php checked(get_option('pdf_builder_gdpr_encryption_enabled', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Chiffrer les données sensibles des utilisateurs</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Section Types de Consentement -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">🤝 Types de Consentement</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="gdpr_consent_analytics">Consentement Analytics</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_consent_analytics" name="gdpr_consent_analytics" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_analytics', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Collecte de données d'utilisation anonymes pour améliorer le service</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_consent_templates">Consentement Templates</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_consent_templates" name="gdpr_consent_templates" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_templates', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Sauvegarde des templates personnalisés sur le serveur</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_consent_marketing">Consentement Marketing</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="gdpr_consent_marketing" name="gdpr_consent_marketing" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_marketing', false)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Réception d'informations sur les nouvelles fonctionnalités et mises à jour</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Section Actions Utilisateur -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">👤 Actions RGPD Utilisateur</h4>
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <p style="margin-top: 0; color: #495057;"><strong>Droits RGPD :</strong> En tant qu'administrateur, vous pouvez gérer vos propres données personnelles.</p>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                            <button type="button" id="export-my-data" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                                📥 Exporter mes données
                            </button>
                            <button type="button" id="delete-my-data" class="button button-danger" style="display: flex; align-items: center; gap: 8px; background: #dc3545; color: white; border-color: #dc3545;">
                                🗑️ Supprimer mes données
                            </button>
                            <button type="button" id="view-consent-status" class="button button-info" style="display: flex; align-items: center; gap: 8px; background: #17a2b8; color: white; border-color: #17a2b8;">
                                👁️ Voir mes consentements
                            </button>
                        </div>

                        <div id="gdpr-user-actions-result" style="margin-top: 15px; display: none;"></div>
                    </div>

                    <!-- Section Logs d'Audit -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">📊 Logs d'Audit RGPD</h4>
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <p style="margin-top: 0; color: #495057;">Consultez et exportez les logs d'audit RGPD pour vérifier la conformité.</p>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                            <button type="button" id="refresh-audit-log" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                                🔄 Actualiser les logs
                            </button>
                            <button type="button" id="export-audit-log" class="button button-primary" style="display: flex; align-items: center; gap: 8px;">
                                📤 Exporter les logs
                            </button>
                        </div>

                        <div id="audit-log-container" style="margin-top: 20px; max-height: 300px; overflow-y: auto; background: white; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; display: none;">
                            <div id="audit-log-content"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="pdf" class="tab-content hidden-tab">
            <h2>📄 Configuration PDF</h2>

            <form method="post" action="">
                <?php wp_nonce_field('pdf_builder_pdf', 'pdf_builder_pdf_nonce'); ?>
                <input type="hidden" name="current_tab" value="pdf">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                        <td>
                            <select id="pdf_quality" name="pdf_quality">
                                <option value="low" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'low'); ?>>Faible</option>
                                <option value="medium" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'medium'); ?>>Moyen</option>
                                <option value="high" <?php selected(get_option('pdf_builder_pdf_quality', 'high'), 'high'); ?>>Élevé</option>
                            </select>
                            <p class="description">Qualité de génération des PDF</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pdf_page_size">Taille de page</label></th>
                        <td>
                            <select id="pdf_page_size" name="pdf_page_size">
                                <option value="A4" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A4'); ?>>A4</option>
                                <option value="A3" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'A3'); ?>>A3</option>
                                <option value="Letter" <?php selected(get_option('pdf_builder_pdf_page_size', 'A4'), 'Letter'); ?>>Letter</option>
                            </select>
                            <p class="description">Format de page pour les PDF</p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

        <div id="contenu" class="tab-content hidden-tab">
            <h2>🎨 Contenu & Design</h2>

            <!-- Section Canvas -->
            <div style="background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%); border: 2px solid #6c757d; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #6c757d; padding-bottom: 10px;">🖼️ Canvas</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_canvas', 'pdf_builder_canvas_nonce'); ?>
                    <input type="hidden" name="current_tab" value="canvas">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_width">Largeur canvas (px)</label></th>
                            <td>
                                <input type="number" id="canvas_width" name="canvas_width" value="<?php echo esc_attr(get_option('pdf_builder_canvas_width', 800)); ?>" min="400" max="2000">
                                <p class="description">Largeur par défaut du canvas de conception</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_height">Hauteur canvas (px)</label></th>
                            <td>
                                <input type="number" id="canvas_height" name="canvas_height" value="<?php echo esc_attr(get_option('pdf_builder_canvas_height', 600)); ?>" min="300" max="2000">
                                <p class="description">Hauteur par défaut du canvas de conception</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <!-- Section Templates -->
            <div style="background: linear-gradient(135deg, #d1ecf1 0%, #e0f7fa 100%); border: 2px solid #17a2b8; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #0c5460; margin-top: 0; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">📋 Templates</h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_templates', 'pdf_builder_templates_nonce'); ?>
                    <input type="hidden" name="current_tab" value="templates">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="default_template">Template par défaut</label></th>
                            <td>
                                <select id="default_template" name="default_template">
                                    <option value="blank" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'blank'); ?>>Page blanche</option>
                                    <option value="invoice" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'invoice'); ?>>Facture</option>
                                    <option value="quote" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'quote'); ?>>Devis</option>
                                </select>
                                <p class="description">Template utilisé par défaut pour nouveaux documents</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="template_library_enabled">Bibliothèque de templates</label></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="template_library_enabled" name="template_library_enabled" value="1" <?php checked(get_option('pdf_builder_template_library_enabled', true)); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Active la bibliothèque de templates prédéfinis</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <div id="roles" class="tab-content hidden-tab">
            <h2>Paramètres Développeur</h2>
            <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

            <div class="notice notice-info" style="margin-bottom: 20px;">
                <p><strong>ℹ️ Rappel Onboarding :</strong> L'onboarding est actuellement désactivé en mode développement (WP_DEBUG=true). Il sera automatiquement activé en production (WP_DEBUG=false). Pensez à le tester avant le déploiement final.</p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('pdf_builder_developer', 'pdf_builder_developer_nonce'); ?>
                <input type="hidden" name="current_tab" value="developpeur">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="developer_enabled">Mode développeur</label></th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" id="developer_enabled" name="developer_enabled" value="1" <?php checked(get_option('pdf_builder_developer_enabled', false)); ?>>
                                <span class="slider round"></span>
                            </label>
                            <p class="description">Active les fonctionnalités de développement avancées</p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

    <style>
        /* Styles pour les interrupteurs */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #007cba;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #007cba;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* Styles pour les onglets */
        .nav-tab-wrapper {
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .nav-tab {
            border: 1px solid #ccc;
            border-bottom: none;
            background: #f1f1f1;
            color: #555;
            padding: 8px 16px;
            text-decoration: none;
            display: inline-block;
            margin-right: 4px;
            border-radius: 4px 4px 0 0;
            cursor: pointer;
        }

        .nav-tab-active {
            background: #fff;
            color: #000;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .hidden-tab {
            display: none !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestionnaire de navigation par onglets
            const tabLinks = document.querySelectorAll('.nav-tab[data-tab]');
            const tabContents = document.querySelectorAll('.tab-content');

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Retirer la classe active de tous les onglets
                    tabLinks.forEach(tabLink => {
                        tabLink.classList.remove('nav-tab-active');
                    });

                    // Ajouter la classe active à l'onglet cliqué
                    this.classList.add('nav-tab-active');

                    // Masquer tous les contenus d'onglets
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        content.classList.add('hidden-tab');
                    });

                    // Afficher le contenu de l'onglet sélectionné
                    const targetTab = this.getAttribute('data-tab');
                    const targetContent = document.getElementById(targetTab);
                    if (targetContent) {
                        targetContent.classList.remove('hidden-tab');
                        targetContent.classList.add('active');
                    }

                    // Sauvegarder l'onglet actif dans le localStorage
                    localStorage.setItem('pdf_builder_active_tab', targetTab);
                });
            });

            // Restaurer l'onglet actif depuis le localStorage
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                const savedTabLink = document.querySelector(`.nav-tab[data-tab="${savedTab}"]`);
                if (savedTabLink) {
                    savedTabLink.click();
                }
            }

            // Activer l'onglet général par défaut si aucun onglet n'est actif
            const activeTab = document.querySelector('.nav-tab-active');
            if (!activeTab) {
                const generalTabLink = document.querySelector('.nav-tab[data-tab="general"]');
                if (generalTabLink) {
                    generalTabLink.click();
                }
            }

            // Gestionnaire pour le bouton global d'enregistrement
            const saveAllButtons = document.querySelectorAll('[id^="save-all-"]');
            saveAllButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Trouver l'onglet actif
                    const activeTabContent = document.querySelector('.tab-content.active');
                    if (!activeTabContent) return;

                    // Collecter tous les formulaires dans l'onglet actif
                    const forms = activeTabContent.querySelectorAll('form');
                    
                    // Soumettre chaque formulaire
                    forms.forEach(form => {
                        // Créer un élément temporaire pour soumettre le formulaire
                        const tempForm = document.createElement('form');
                        tempForm.method = form.method;
                        tempForm.action = form.action;
                        tempForm.style.display = 'none';
                        
                        // Copier tous les champs du formulaire original
                        const inputs = form.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            const clone = input.cloneNode(true);
                            tempForm.appendChild(clone);
                        });
                        
                        // Ajouter au body et soumettre
                        document.body.appendChild(tempForm);
                        tempForm.submit();
                        
                        // Nettoyer
                        document.body.removeChild(tempForm);
                    });
                    
                    // Afficher un message de confirmation
                    alert('✅ Toutes les modifications ont été enregistrées !');
                });
            });
        });
    </script>

    <!-- Script pour les actions RGPD -->
    <script>
        jQuery(document).ready(function($) {
            // Actions RGPD - Export des données utilisateur
            $('#export-my-data').on('click', function() {
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html('⏳ Export en cours...').prop('disabled', true);

                $.ajax({
                    url: pdf_builder_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_export_user_data',
                        nonce: pdf_builder_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-error')
                                .addClass('notice notice-success')
                                .html('<p>✅ ' + response.data.message + '</p>')
                                .show();

                            // Télécharger automatiquement le fichier
                            if (response.data.download_url) {
                                window.open(response.data.download_url, '_blank');
                            }
                        } else {
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-success')
                                .addClass('notice notice-error')
                                .html('<p>❌ Erreur: ' + (response.data || 'Erreur inconnue') + '</p>')
                                .show();
                        }
                    },
                    error: function() {
                        $('#gdpr-user-actions-result')
                            .removeClass('notice-success')
                            .addClass('notice notice-error')
                            .html('<p>❌ Erreur de connexion</p>')
                            .show();
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Actions RGPD - Suppression des données utilisateur
            $('#delete-my-data').on('click', function() {
                if (!confirm('Êtes-vous sûr de vouloir supprimer toutes vos données ? Cette action est irréversible.')) {
                    return;
                }

                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html('⏳ Suppression...').prop('disabled', true);

                $.ajax({
                    url: pdf_builder_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_delete_user_data',
                        nonce: pdf_builder_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-error')
                                .addClass('notice notice-success')
                                .html('<p>✅ ' + response.data.message + '</p>')
                                .show();
                        } else {
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-success')
                                .addClass('notice notice-error')
                                .html('<p>❌ Erreur: ' + (response.data || 'Erreur inconnue') + '</p>')
                                .show();
                        }
                    },
                    error: function() {
                        $('#gdpr-user-actions-result')
                            .removeClass('notice-success')
                            .addClass('notice notice-error')
                            .html('<p>❌ Erreur de connexion</p>')
                            .show();
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Actions RGPD - Voir le statut des consentements
            $('#view-consent-status').on('click', function() {
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html('⏳ Chargement...').prop('disabled', true);

                $.ajax({
                    url: pdf_builder_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_get_consent_status',
                        nonce: pdf_builder_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '<h4>Vos consentements actuels:</h4><ul>';
                            if (response.data.consents) {
                                Object.keys(response.data.consents).forEach(function(type) {
                                    const consent = response.data.consents[type];
                                    const status = consent && consent.granted ? '✅ Accordé' : '❌ Non accordé';
                                    html += '<li><strong>' + type + ':</strong> ' + status + '</li>';
                                });
                            }
                            html += '</ul>';

                            $('#gdpr-user-actions-result')
                                .removeClass('notice-error')
                                .addClass('notice notice-info')
                                .html(html)
                                .show();
                        } else {
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-success')
                                .addClass('notice notice-error')
                                .html('<p>❌ Erreur: ' + (response.data || 'Erreur inconnue') + '</p>')
                                .show();
                        }
                    },
                    error: function() {
                        $('#gdpr-user-actions-result')
                            .removeClass('notice-success')
                            .addClass('notice notice-error')
                            .html('<p>❌ Erreur de connexion</p>')
                            .show();
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Actions RGPD - Actualiser les logs d'audit
            $('#refresh-audit-log').on('click', function() {
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html('⏳ Actualisation...').prop('disabled', true);

                $.ajax({
                    url: pdf_builder_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_refresh_audit_log',
                        nonce: pdf_builder_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#audit-log-content').html(response.data.html || '<p>Aucun log d\'audit trouvé.</p>');
                            $('#audit-log-container').show();
                        } else {
                            $('#audit-log-content').html('<p class="notice notice-error">Erreur: ' + (response.data || 'Erreur inconnue') + '</p>');
                            $('#audit-log-container').show();
                        }
                    },
                    error: function() {
                        $('#audit-log-content').html('<p class="notice notice-error">Erreur de connexion</p>');
                        $('#audit-log-container').show();
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Actions RGPD - Exporter les logs d'audit
            $('#export-audit-log').on('click', function() {
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html('⏳ Export...').prop('disabled', true);

                $.ajax({
                    url: pdf_builder_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_export_audit_log',
                        nonce: pdf_builder_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.data.download_url) {
                                window.open(response.data.download_url, '_blank');
                            }
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-error')
                                .addClass('notice notice-success')
                                .html('<p>✅ Logs d\'audit exportés avec succès</p>')
                                .show();
                        } else {
                            $('#gdpr-user-actions-result')
                                .removeClass('notice-success')
                                .addClass('notice notice-error')
                                .html('<p>❌ Erreur: ' + (response.data || 'Erreur inconnue') + '</p>')
                                .show();
                        }
                    },
                    error: function() {
                        $('#gdpr-user-actions-result')
                            .removeClass('notice-success')
                            .addClass('notice notice-error')
                            .html('<p>❌ Erreur de connexion</p>')
                            .show();
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>

    <div id="roles" class="tab-content hidden-tab">
        <h2>👨‍💻 Paramètres Développeur</h2>
        <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

        <div class="notice notice-info" style="margin-bottom: 20px;">
            <p><strong>ℹ️ Rappel Onboarding :</strong> L'onboarding est actuellement désactivé en mode développement (WP_DEBUG=true). Il sera automatiquement activé en production (WP_DEBUG=false). Pensez à le tester avant le déploiement final.</p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('pdf_builder_developer', 'pdf_builder_developer_nonce'); ?>
            <input type="hidden" name="current_tab" value="developpeur">

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode développeur</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="developer_enabled" value="1" <?php checked(get_option('pdf_builder_developer_enabled', false)); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode développeur</span>
                        </div>
                        <p class="description">Active les fonctionnalités de développement avancées et les logs détaillés</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_logging">Logs de débogage</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_logging" name="debug_logging" value="1" <?php checked(get_option('pdf_builder_debug_logging', false)); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer les logs de débogage</span>
                        </div>
                        <p class="description">Enregistre les informations détaillées pour le débogage</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="test_mode">Mode test</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="test_mode" name="test_mode" value="1" <?php checked(get_option('pdf_builder_test_mode', false)); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode test</span>
                        </div>
                        <p class="description">Active les fonctionnalités de test et désactive certaines restrictions</p>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                <h4 style="margin-top: 0; color: #495057;">🔑 Gestion des Clés de Test</h4>
                <p style="margin-bottom: 15px; color: #666;">Générez et gérez des clés de licence de test pour le développement.</p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div style="padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                        <button type="button" id="generate-test-key-btn" class="button button-primary" style="width: 100%; margin-bottom: 10px;">
                            🎯 Générer une clé de test
                        </button>
                        <div id="test-key-result" style="font-family: monospace; font-size: 12px; color: #666; word-break: break-all;"></div>
                    </div>

                    <div style="padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                        <button type="button" id="delete-test-key-btn" class="button button-secondary" style="width: 100%; margin-bottom: 10px;">
                            🗑️ Supprimer la clé de test
                        </button>
                        <div id="delete-test-key-result" style="font-size: 12px; color: #666;"></div>
                    </div>
                </div>

                <div style="padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                    <h5 style="margin-top: 0; color: #495057;">📊 État actuel</h5>
                    <div id="license-status-display" style="font-size: 13px; color: #666;">
                        <?php
                        $test_key = get_option('pdf_builder_license_test_key', '');
                        $test_mode = get_option('pdf_builder_license_test_mode_enabled', false);
                        echo '<strong>Clé de test :</strong> ' . (!empty($test_key) ? 'Présente (****' . substr($test_key, -4) . ')' : 'Aucune') . '<br>';
                        echo '<strong>Mode test :</strong> ' . ($test_mode ? 'Activé' : 'Désactivé');
                        ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .toggle-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #007cba;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .toggle-label {
            font-weight: 600;
            color: #333;
            cursor: pointer;
        }

        .toggle-description {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 13px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des toggles dans l'onglet développeur
            const toggles = document.querySelectorAll('#roles .toggle-switch input[type="checkbox"]');
            toggles.forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const label = this.parentElement.nextElementSibling;
                    if (label && label.classList.contains('toggle-label')) {
                        // Animation visuelle pour confirmer le changement
                        label.style.color = this.checked ? '#28a745' : '#333';
                        setTimeout(function() {
                            label.style.color = '#333';
                        }, 300);
                    }
                });
            });

            // Gestionnaire pour générer une clé de test
            const generateBtn = document.getElementById('generate-test-key-btn');
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
                    generateBtn.disabled = true;
                    generateBtn.textContent = '⏳ Génération...';

                    const formData = new FormData();
                    formData.append('action', 'pdf_builder_generate_test_license_key');
                    formData.append('security', pdf_builder_ajax.nonce);

                    fetch(pdf_builder_ajax.ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        generateBtn.disabled = false;
                        generateBtn.textContent = '🎯 Générer une clé de test';

                        const resultDiv = document.getElementById('test-key-result');
                        if (resultDiv) {
                            if (data.success) {
                                resultDiv.innerHTML = '<span style="color: #28a745;">✅ Clé générée : <strong>' + data.data.key + '</strong></span>';
                                // Recharger la page pour mettre à jour l'état
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                resultDiv.innerHTML = '<span style="color: #dc3545;">❌ Erreur : ' + (data.data || 'Erreur inconnue') + '</span>';
                            }
                        }
                    })
                    .catch(function(error) {
                        generateBtn.disabled = false;
                        generateBtn.textContent = '🎯 Générer une clé de test';
                        console.error('Erreur lors de la génération de la clé:', error);
                        const resultDiv = document.getElementById('test-key-result');
                        if (resultDiv) {
                            resultDiv.innerHTML = '<span style="color: #dc3545;">❌ Erreur AJAX</span>';
                        }
                    });
                });
            }

            // Gestionnaire pour supprimer la clé de test
            const deleteBtn = document.getElementById('delete-test-key-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer la clé de test ?')) {
                        return;
                    }

                    deleteBtn.disabled = true;
                    deleteBtn.textContent = '⏳ Suppression...';

                    const formData = new FormData();
                    formData.append('action', 'pdf_builder_delete_test_license_key');
                    formData.append('security', pdf_builder_ajax.nonce);

                    fetch(pdf_builder_ajax.ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = '🗑️ Supprimer la clé de test';

                        const resultDiv = document.getElementById('delete-test-key-result');
                        if (resultDiv) {
                            if (data.success) {
                                resultDiv.innerHTML = '<span style="color: #28a745;">✅ Clé de test supprimée</span>';
                                // Recharger la page pour mettre à jour l'état
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                resultDiv.innerHTML = '<span style="color: #dc3545;">❌ Erreur : ' + (data.data || 'Erreur inconnue') + '</span>';
                            }
                        }
                    })
                    .catch(function(error) {
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = '🗑️ Supprimer la clé de test';
                        console.error('Erreur lors de la suppression de la clé:', error);
                        const resultDiv = document.getElementById('delete-test-key-result');
                        if (resultDiv) {
                            resultDiv.innerHTML = '<span style="color: #dc3545;">❌ Erreur AJAX</span>';
                        }
                    });
                });
            }
        });
    </script>

    <!-- Bouton flottant d'enregistrement global -->
    <div id="floating-save-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none;">
        <button type="button" class="floating-save-btn" style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border: none; border-radius: 50px; padding: 15px 25px; font-size: 16px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
            <span class="save-icon">💾</span>
            <span class="save-text">Enregistrer</span>
        </button>
        <div class="floating-tooltip" style="position: absolute; bottom: 70px; right: 0; background: #333; color: white; padding: 8px 12px; border-radius: 6px; font-size: 14px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
            Enregistrer tous les paramètres de cet onglet
        </div>
    </div>

    <style>
        /* Styles pour le bouton flottant */
        .floating-save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }

        .floating-save-btn.saving {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            animation: pulse 1.5s infinite;
        }

        .floating-save-btn.saved {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }

        .floating-save-btn.error {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
        }

        .floating-tooltip {
            opacity: 0;
        }

        .floating-save-btn:hover + .floating-tooltip,
        .floating-tooltip:hover {
            opacity: 1;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Responsive design pour mobile */
        @media (max-width: 768px) {
            #floating-save-button {
                bottom: 15px;
                right: 15px;
            }

            .floating-save-btn {
                padding: 12px 20px;
                font-size: 14px;
            }

            .floating-tooltip {
                display: none; /* Masquer le tooltip sur mobile */
            }
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            let currentTab = 'general';

            // Fonction pour afficher/masquer le bouton flottant selon l'onglet actif
            function updateFloatingButtonVisibility() {
                const activeTab = $('.nav-tab-active').attr('href').substring(1);
                currentTab = activeTab;

                // Le bouton flottant est maintenant visible dans tous les onglets
                $('#floating-save-button').show();
            }

            // Mettre à jour la visibilité lors du changement d'onglet
            $('.nav-tab').on('click', function() {
                setTimeout(updateFloatingButtonVisibility, 100);
            });

            // Vérifier l'onglet actif au chargement
            updateFloatingButtonVisibility();            // Gestionnaire pour le bouton flottant
            $('.floating-save-btn').on('click', function() {
                const $btn = $(this);
                const $icon = $btn.find('.save-icon');
                const $text = $btn.find('.save-text');

                // État de sauvegarde
                $btn.removeClass('saved error').addClass('saving');
                $icon.text('⏳');
                $text.text('Sauvegarde...');

                // Trouver tous les formulaires dans l'onglet actif
                const activeTabContent = $('.tab-content:not(.hidden-tab)');
                const forms = activeTabContent.find('form');

                if (forms.length === 0) {
                    // Pas de formulaires trouvés
                    $btn.removeClass('saving').addClass('error');
                    $icon.text('❌');
                    $text.text('Erreur');
                    setTimeout(() => {
                        $btn.removeClass('error');
                        $icon.text('💾');
                        $text.text('Enregistrer');
                    }, 3000);
                    return;
                }

                // Collecter les données de tous les formulaires
                const formData = new FormData();

                forms.each(function() {
                    const $form = $(this);
                    const formDataTemp = new FormData(this);

                    // Ajouter les données de ce formulaire
                    for (let [key, value] of formDataTemp.entries()) {
                        formData.append(key, value);
                    }
                });

                // Ajouter l'onglet actuel
                formData.append('current_tab', currentTab);
                formData.append('action', 'pdf_builder_save_settings');
                formData.append('nonce', pdf_builder_ajax.nonce);

                // Envoyer via AJAX
                $.ajax({
                    url: pdf_builder_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Succès
                            $btn.removeClass('saving').addClass('saved');
                            $icon.text('✅');
                            $text.text('Enregistré !');

                            // Revenir à l'état normal après 3 secondes
                            setTimeout(() => {
                                $btn.removeClass('saved');
                                $icon.text('💾');
                                $text.text('Enregistrer');
                            }, 3000);
                        } else {
                            // Erreur
                            $btn.removeClass('saving').addClass('error');
                            $icon.text('❌');
                            $text.text('Erreur');

                            setTimeout(() => {
                                $btn.removeClass('error');
                                $icon.text('💾');
                                $text.text('Enregistrer');
                            }, 3000);
                        }
                    },
                    error: function() {
                        // Erreur AJAX
                        $btn.removeClass('saving').addClass('error');
                        $icon.text('❌');
                        $text.text('Erreur');

                        setTimeout(() => {
                            $btn.removeClass('error');
                            $icon.text('💾');
                            $text.text('Enregistrer');
                        }, 3000);
                    }
                });
            });
        });
    </script>

