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

    // Handle AJAX clear cache request
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
        <a href="#performance" class="nav-tab" data-tab="performance">
            <span class="tab-icon">🚀</span>
            <span class="tab-text">Performance</span>
        </a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">
            <span class="tab-icon">📄</span>
            <span class="tab-text">PDF</span>
        </a>
        <a href="#securite" class="nav-tab" data-tab="securite">
            <span class="tab-icon">🔒</span>
            <span class="tab-text">Sécurité</span>
        </a>
        <a href="#roles" class="nav-tab" data-tab="roles">
            <span class="tab-icon">👥</span>
            <span class="tab-text">Rôles</span>
        </a>
        <a href="#notifications" class="nav-tab" data-tab="notifications">
            <span class="tab-icon">🔔</span>
            <span class="tab-text">Notifications</span>
        </a>
        <a href="#canvas" class="nav-tab" data-tab="canvas">
            <span class="tab-icon">🎨</span>
            <span class="tab-text">Canvas</span>
        </a>
        <a href="#templates" class="nav-tab" data-tab="templates">
            <span class="tab-icon">📋</span>
            <span class="tab-text">Templates</span>
        </a>
        <a href="#maintenance" class="nav-tab" data-tab="maintenance">
            <span class="tab-icon">🔧</span>
            <span class="tab-text">Maintenance</span>
        </a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur">
            <span class="tab-icon">👨‍💻</span>
            <span class="tab-text">Développeur</span>
        </a>
    </div>

        <div id="general" class="tab-content">
            <form method="post" id="general-form" action="">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
                <input type="hidden" name="submit" value="1">

                <h2>Paramètres Généraux</h2>
                <p style="color: #666;">Paramètres de base pour la génération PDF. Pour le cache et la sécurité, voir les onglets Performance et Sécurité.</p>
                <h3 class="section-title">📋 Cache</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" <?php checked($settings['cache_enabled'] ?? false); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Activer le cache</span>
                            </div>
                            <div class="toggle-description">Améliore les performances en mettant en cache les données</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                        <td>
                            <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval($settings['cache_ttl'] ?? 3600); ?>" min="0" max="86400" />
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
                            <div id="cache-test-output" style="display: none; margin-top: 10px; padding: 15px; background: #e7f5e9; border-left: 4px solid #28a745; border-radius: 4px; color: #155724;"></div>
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

                <h3 class="section-title">🏢 Informations Entreprise</h3>

                <div style="padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h4 style="margin-top: 0; color: #155724;">📋 Informations récupérées automatiquement de WooCommerce</h4>
                <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
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

                    <table class="form-table" style="background: white; padding: 15px; border-radius: 6px;">
                        <tr>
                            <th scope="row"><label for="company_phone_manual">Téléphone</label></th>
                            <td>
                                <input type="text" id="company_phone_manual" name="company_phone_manual"
                                    value="<?php echo esc_attr($settings['company_phone_manual'] ?? ''); ?>"
                                    placeholder="+33 1 23 45 67 89" />
                                <p class="description">Téléphone de l'entreprise</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="company_siret">Numéro SIRET</label></th>
                            <td>
                                <input type="text" id="company_siret" name="company_siret"
                                    value="<?php echo esc_attr($settings['company_siret'] ?? ''); ?>"
                                    placeholder="123 456 789 00012" />
                                <p class="description">Numéro SIRET de l'entreprise</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="company_vat">Numéro TVA</label></th>
                            <td>
                                <input type="text" id="company_vat" name="company_vat"
                                    value="<?php echo esc_attr($settings['company_vat'] ?? ''); ?>"
                                    placeholder="FR 12 345 678 901" />
                                <p class="description">Numéro de TVA intracommunautaire</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="company_rcs">RCS</label></th>
                            <td>
                                <input type="text" id="company_rcs" name="company_rcs"
                                    value="<?php echo esc_attr($settings['company_rcs'] ?? ''); ?>"
                                    placeholder="Lyon B 123 456 789" />
                                <p class="description">Numéro RCS (Registre du Commerce et des Sociétés)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="company_capital">Capital social</label></th>
                            <td>
                                <input type="text" id="company_capital" name="company_capital"
                                    value="<?php echo esc_attr($settings['company_capital'] ?? ''); ?>"
                                    placeholder="10 000 €" />
                                <p class="description">Montant du capital social de l'entreprise</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <h3 class="section-title">📄 Paramètres PDF</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                        <td>
                            <select id="pdf_quality" name="pdf_quality">
                                <option value="low" <?php selected($settings['pdf_quality'] ?? 'high', 'low'); ?>>Faible (fichiers plus petits)</option>
                                <option value="medium" <?php selected($settings['pdf_quality'] ?? 'high', 'medium'); ?>>Moyen</option>
                                <option value="high" <?php selected($settings['pdf_quality'] ?? 'high', 'high'); ?>>Élevée (meilleure qualité)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_format">Format PDF par défaut</label></th>
                        <td>
                            <select id="default_format" name="default_format">
                                <option value="A4" <?php selected($settings['default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                                <option value="A3" <?php selected($settings['default_format'] ?? 'A4', 'A3'); ?>>A3</option>
                                <option value="Letter" <?php selected($settings['default_format'] ?? 'A4', 'Letter'); ?>>Letter</option>
                                <option value="Legal" <?php selected($settings['default_format'] ?? 'A4', 'Legal'); ?>>Legal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_orientation">Orientation par défaut</label></th>
                        <td>
                            <select id="default_orientation" name="default_orientation">
                                <option value="portrait" <?php selected($settings['default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                                <option value="landscape" <?php selected($settings['default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                            </select>
                        </td>
                    </tr>
                </table>

            </form>
        </div>

        <div id="licence" class="tab-content hidden-tab">
            <form method="post" id="licence-form" action="">
                <input type="hidden" name="current_tab" value="licence">
                    <h2 style="color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">🔐 Gestion de la Licence</h2>

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
                        wp_die('<div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 20px; color: #856404; font-family: Arial, sans-serif;">
                                <h2 style="margin-top: 0; color: #856404;">⚠️ Mode DÉMO</h2>
                                <p><strong>La validation des clés premium n\'est pas encore active.</strong></p>
                                <p>Pour tester les fonctionnalités premium, veuillez :</p>
                                <ol>
                                    <li>Allez à l\'onglet <strong>Développeur</strong></li>
                                    <li>Cliquez sur <strong>Générer une clé de test</strong></li>
                                    <li>La clé TEST s\'activera automatiquement</li>
                                </ol>
                                <p><a href="' . admin_url('admin.php?page=pdf-builder-pro-settings&tab=developer') . '" style="background: #ffc107; color: #856404; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;">↻ Aller au mode Développeur</a></p>
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
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e5e5e5; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h3 style="margin-top: 0; color: #007cba; font-size: 22px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">📊 Statut de la Licence</h3>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 25px;">
                            <!-- Carte Statut Principal -->
                            <div style="border: 3px solid <?php echo $is_premium ? '#28a745' : '#6c757d'; ?>; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, <?php echo $is_premium ? '#d4edda' : '#f8f9fa'; ?> 0%, <?php echo $is_premium ? '#e8f5e9' : '#ffffff'; ?> 100%); box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s;">
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
                            <div style="border: 3px solid #ffc107; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); box-shadow: 0 4px 6px rgba(255,193,7,0.2); transition: transform 0.2s;">
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
                            <div style="border: 3px solid #17a2b8; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #d1ecf1 0%, #e0f7fa 100%); box-shadow: 0 4px 6px rgba(23,162,184,0.2); transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #0c5460; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Expire le</div>
                                <div style="font-size: 26px; font-weight: 900; color: #0c5460; margin-bottom: 8px;">
                                    <?php echo date('d/m/Y', strtotime($license_expires)); ?>
                                </div>
                                <div style="font-size: 12px; color: #0c5460; font-style: italic;">
                                    <?php
                                    $now = new DateTime();
                                    $expires = new DateTime($license_expires);
                                    $diff = $now->diff($expires);
                                    if ($diff->invert) {
                                        echo '❌ Expiré il y a ' . $diff->days . ' jours';
                                    } else {
                                        echo '✓ Valide pendant ' . $diff->days . ' jours';
                                    }
                                    ?>
                                </div>
                            </div>
                                <?php
                            endif; ?>
                        </div>

                    <?php
                        // Bannière d'alerte si expiration dans moins de 30 jours
                        if ($is_premium && !empty($license_expires)) {
                            $now = new DateTime();
                            $expires = new DateTime($license_expires);
                            $diff = $now->diff($expires);

                            if (!$diff->invert && $diff->days <= 30 && $diff->days > 0) {
                                ?>
                                <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 20px; box-shadow: 0 3px 8px rgba(255,193,7,0.2);">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div style="font-size: 32px; flex-shrink: 0;">⏰</div>
                                        <div>
                                            <strong style="font-size: 16px; color: #856404; display: block; margin-bottom: 4px;">Votre licence expire bientôt</strong>
                                            <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                                Votre licence Premium expire dans <strong><?php echo $diff->days; ?> jour<?php echo $diff->days > 1 ? 's' : ''; ?></strong> (le <?php echo date('d/m/Y', strtotime($license_expires)); ?>).
                                                Renouvelez dès maintenant pour continuer à bénéficier de toutes les fonctionnalités premium.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <!-- Détails de la clé -->
                        <?php if ($is_premium || !empty($test_key)) :
                            ?>
                        <div style="background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border-left: 5px solid #007bff; border-radius: 8px; padding: 20px; margin-top: 25px; box-shadow: 0 2px 4px rgba(0,123,255,0.1);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4 style="margin: 0; color: #004085; font-size: 16px;">🔐 Détails de la Clé</h4>
                                <?php if ($is_premium) :
                                    ?>
                                <form method="post" style="display: inline;" id="deactivate_form">
                                    <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                                    <button type="button" name="deactivate_license" class="button button-secondary" style="background-color: #dc3545 !important; border-color: #dc3545 !important; color: white !important; font-weight: bold !important; padding: 8px 16px !important; font-size: 13px !important;"
                                            onclick="showDeactivateModal()">
                                        Désactiver
                                    </button>
                                </form>
                                    <?php
                                endif; ?>
                            </div>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Site actuel :</td>
                                    <td style="padding: 8px 0;">
                                        <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 3px; border: 1px solid #ddd; color: #007bff;">
                                            <?php echo esc_html(home_url()); ?>
                                        </code>
                                    </td>
                                </tr>

                                <?php if ($is_premium && $license_key) :
                                    ?>
                                <tr style="border-bottom: 2px solid #cce5ff;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Clé Premium :</td>
                                    <td style="padding: 8px 0; font-family: monospace;">
                                        <code style="background: #fff; padding: 4px 8px; border-radius: 3px; border: 1px solid #ddd;">
                                            <?php
                                            $key = $license_key;
                                            $visible_start = substr($key, 0, 6);
                                            $visible_end = substr($key, -6);
                                            echo $visible_start . '••••••••••••••••' . $visible_end;
                                            ?>
                                        </code>
                                        <span style="margin-left: 10px; cursor: pointer; color: #007bff;" onclick="navigator.clipboard.writeText('<?php echo esc_js($license_key); ?>'); alert('✅ Clé copiée !'); ">📋 Copier</span>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>

                                <?php if (!empty($test_key)) :
                                    ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Clé de Test :</td>
                                    <td style="padding: 8px 0; font-family: monospace;">
                                        <code style="background: #fff3cd; padding: 4px 8px; border-radius: 3px; border: 1px solid #ffc107;">
                                            <?php
                                            $test = $test_key;
                                            echo substr($test, 0, 6) . '••••••••••••••••' . substr($test, -6);
                                            ?>
                                        </code>
                                        <span style="margin-left: 10px; color: #666; font-size: 12px;"> (Mode Développement)</span>
                                    </td>
                                </tr>
                                    <?php if (!empty($test_key_expires)) :
                                        ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Expire le :</td>
                                    <td style="padding: 8px 0;">
                                        <div style="margin-bottom: 4px;">
                                            <strong><?php echo date('d/m/Y', strtotime($test_key_expires)); ?></strong>
                                        </div>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php
                                            $now = new DateTime();
                                            $expires = new DateTime($test_key_expires);
                                            $diff = $now->diff($expires);
                                            if ($diff->invert) {
                                                echo '❌ Expiré il y a ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            } else {
                                                echo '✓ Valide pendant ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                        <?php
                                    endif; ?>
                                    <?php
                                endif; ?>

                                <?php if ($is_premium && $license_activated_at) :
                                    ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500;">Activée le :</td>
                                    <td style="padding: 8px 0;">
                                        <?php echo date('d/m/Y à H:i', strtotime($license_activated_at)); ?>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>

                                <tr>
                                    <td style="padding: 8px 0; font-weight: 500;">Statut :</td>
                                    <td style="padding: 8px 0;">
                                        <?php
                                        if (!empty($test_key)) {
                                            echo '<span style="background: #ffc107; color: #000; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">🧪 MODE TEST</span>';
                                        } elseif ($is_premium) {
                                            echo '<span style="background: #28a745; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">✅ ACTIVE</span>';
                                        } else {
                                            echo '<span style="background: #6c757d; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">○ GRATUIT</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <?php if ($is_premium && !empty($license_expires)) :
                                    ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500;">Expire le :</td>
                                    <td style="padding: 8px 0;">
                                        <div style="margin-bottom: 4px;">
                                            <strong><?php echo date('d/m/Y', strtotime($license_expires)); ?></strong>
                                        </div>
                                        <div style="font-size: 12px; color: #666;">
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
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>
                            </table>
                        </div>
                            <?php
                        endif; ?>
                </div>

                    <!-- Activation/Désactivation - Mode DEMO ou Gestion TEST -->
                    <?php if (!$is_premium) :
                        ?>
                    <!-- Mode DÉMO : Pas de licence -->
                    <div style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; border-radius: 12px; padding: 35px; margin-bottom: 20px; box-shadow: 0 3px 8px rgba(255,193,7,0.2);">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                            <div style="font-size: 50px;">🧪</div>
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #856404; font-size: 26px; font-weight: 700;">Mode DÉMO - Clés de Test Uniquement</h3>
                                <p style="margin: 0; color: #856404; font-size: 15px; line-height: 1.5;">La validation des clés premium n'est pas encore active. Utilisez le mode TEST pour explorer les fonctionnalités.</p>
                            </div>
                        </div>

                        <div style="background: rgba(255,193,7,0.15); border-left: 4px solid #ffc107; border-radius: 6px; padding: 20px; margin-bottom: 20px; color: #856404; font-size: 14px; line-height: 1.6;">
                            <strong>✓ Comment tester :</strong>
                            <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                                <li>Allez à l'onglet <strong>Développeur</strong></li>
                                <li>Cliquez sur <strong>🔑 Générer une clé de test</strong></li>
                                <li>La clé TEST s'activera automatiquement</li>
                                <li>Toutes les fonctionnalités premium seront disponibles</li>
                            </ol>
                        </div>

                        <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; border-radius: 6px; padding: 15px; color: #721c24; font-size: 13px;">
                            <strong>⚠️ Note importante :</strong> Les clés premium réelles seront validées une fois le système de licence en production.
                        </div>
                    </div>
                        <?php
                    elseif ($is_test_mode) :
                        ?>
                    <!-- Mode TEST : Gestion de la clé de test -->
                    <div style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; border-radius: 12px; padding: 35px; margin-bottom: 20px; box-shadow: 0 3px 8px rgba(255,193,7,0.2);">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                            <div style="font-size: 50px;">🧪</div>
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #856404; font-size: 26px; font-weight: 700;">Gestion de la Clé de Test</h3>
                                <p style="margin: 0; color: #856404;">Vous testez actuellement avec une clé TEST. Toutes les fonctionnalités premium sont disponibles.</p>
                            </div>
                        </div>

                        <div style="background: rgba(255,193,7,0.15); border-left: 4px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 20px; color: #856404; font-size: 13px;">
                            <strong>ℹ️ Mode Test Actif :</strong> Vous pouvez désactiver cette clé à tout moment depuis la section "Détails de la Clé" ci-dessus, ou générer une nouvelle clé de test depuis l'onglet Développeur.
                        </div>
                    </div>
                        <?php
                    else :
                        ?>
                    <!-- Mode PREMIUM : Gestion de la licence premium -->
                    <div style="background: linear-gradient(135deg, #f0f8f5 0%, #ffffff 100%); border: 2px solid #28a745; border-radius: 12px; padding: 35px; margin-bottom: 20px; box-shadow: 0 3px 8px rgba(40,167,69,0.2);">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                            <div style="font-size: 50px;">🔐</div>
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #155724; font-size: 26px; font-weight: 700;">Gestion de la Licence Premium</h3>
                                <p style="margin: 0; color: #155724;">Votre licence premium est active et valide. Vous pouvez gerer votre licence ci-dessous.</p>
                            </div>
                        </div>

                        <!-- Avertissements et informations -->
                        <div style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; border-radius: 8px; padding: 20px; margin-bottom: 20px; color: #fff; box-shadow: 0 3px 8px rgba(255,193,7,0.3);">
                            <strong style="font-size: 17px; display: flex; align-items: center; gap: 8px; color: #fff;">Savoir :</strong>
                            <ul style="margin: 12px 0 0 0; padding-left: 20px; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                <li style="margin: 6px 0;">Votre licence reste <strong>active pendant un an</strong> a partir de son activation</li>
                                <li style="margin: 6px 0;">Meme apres desactivation, la licence reste valide jusqu'a son expiration</li>
                                <li style="margin: 6px 0;"><strong>Desactivez</strong> pour utiliser la meme cle sur un autre site WordPress</li>
                                <li style="margin: 6px 0;">Une cle ne peut etre active que sur <strong>un seul site a la fois</strong></li>
                            </ul>
                        </div>

                        <form method="post">
                            <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                            <p class="submit" style="margin-top: 20px;">
                                <button type="submit" name="deactivate_license" class="button button-secondary" style="background-color: #dc3545 !important; border-color: #dc3545 !important; color: white !important; font-weight: bold !important; padding: 10px 20px !important; display: block !important; visibility: visible !important; opacity: 1 !important;"
                                        onclick="return confirm('Etes-vous sur de vouloir desactiver cette licence ? Vous pourrez la reactiver ou l\'utiliser sur un autre site.');">
                                    Desactiver la Licence
                                </button>
                            </p>
                        </form>

                        <div style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); border: none; border-radius: 8px; padding: 22px; margin-top: 20px; color: #fff; box-shadow: 0 3px 8px rgba(23,162,184,0.25);">
                            <strong style="font-size: 17px; display: flex; align-items: center; gap: 8px; color: #fff;">Conseil :</strong>
                            <p style="margin: 12px 0 0 0; line-height: 1.6; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">La desactivation permet de reutiliser votre cle sur un autre site, mais ne supprime pas votre acces ici jusqu'a l'expiration de la licence.</p>
                        </div>
                    </div>

                        <?php
                    endif; ?>

                    <!-- Modal de confirmation pour désactivation -->
                    <div id="deactivate_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
                        <div style="background: white; border-radius: 12px; padding: 40px; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                            <h2 style="margin: 0 0 15px 0; color: #333; font-size: 24px;">Désactiver la Licence</h2>
                            <p style="margin: 0 0 20px 0; color: #666; line-height: 1.6;">Êtes-vous sûr de vouloir désactiver cette licence ?</p>
                            <ul style="text-align: left; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; list-style: none;">
                                <li style="margin: 8px 0;">✓ Vous pouvez la réactiver plus tard</li>
                                <li style="margin: 8px 0;">✓ Vous pourrez l'utiliser sur un autre site</li>
                                <li style="margin: 8px 0;">✓ La licence restera valide jusqu'à son expiration</li>
                            </ul>
                            <div style="display: flex; gap: 12px; margin-top: 30px;">
                                <button type="button" style="flex: 1; background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;" onclick="closeDeactivateModal()">
                                    Annuler
                                </button>
                                <button type="button" style="flex: 1; background: #dc3545; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;" onclick="submitDeactivateForm()">
                                    Désactiver
                                </button>
                            </div>
                        </div>
                    </div>

                <script>
                    function showDeactivateModal() {
                        document.getElementById('deactivate_modal').style.display = 'flex';
                        return false;
                    }

                    function closeDeactivateModal() {
                        document.getElementById('deactivate_modal').style.display = 'none';
                    }

                    function submitDeactivateForm() {
                        document.getElementById('deactivate_form').submit();
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
                                formData.append('security', '<?php echo wp_create_nonce('pdf_builder_clear_cache_performance'); ?>');

                                fetch(ajaxurl, {
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
                    });
                </script>

                    <!-- Informations utiles -->
                    <div style="background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%); border: none; border-radius: 12px; padding: 30px; margin-bottom: 30px; color: #fff; box-shadow: 0 4px 12px rgba(23,162,184,0.3);">
                        <h4 style="margin: 0 0 20px 0; color: #fff; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">Informations Utiles</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                            <!-- Site actuel -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Site actuel</div>
                                <code style="background: rgba(255,255,255,0.2); padding: 6px 10px; border-radius: 4px; font-family: monospace; color: #fff; display: block; word-break: break-all; font-size: 12px;"><?php echo esc_html(home_url()); ?></code>
                            </div>

                            <!-- Plan actif -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Plan actif</div>
                                <span style="background: rgba(255,255,255,0.3); color: #fff; padding: 6px 12px; border-radius:  4px; font-weight: bold; font-size: 13px; display: inline-block;"><?php echo !empty($test_key) ? '🧪 Mode Test' : ($is_premium ? '⭐ Premium' : '○ Gratuit'); ?></span>
                            </div>

                            <!-- Version du plugin -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Version du plugin</div>
                                <div style="font-size: 14px; font-weight: bold;"><?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : 'N/A'; ?></div>
                            </div>

                            <?php if ($is_premium) :
                                ?>
                            <!-- Support Premium -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Support</div>
                                <a href="https://pdfbuilderpro.com/support" target="_blank" style="color: #fff; text-decoration: underline; font-weight: 600; font-size: 13px;">Contact Support Premium →</a>
                            </div>

                            <!-- Documentation -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Documentation</div>
                                <a href="https://pdfbuilderpro.com/docs" target="_blank" style="color: #fff; text-decoration: underline; font-weight: 600; font-size: 13px;">Lire la Documentation →</a>
                            </div>
                                <?php
                            endif; ?>
                        </div>
                    </div>

                    <!-- Comparaison des fonctionnalités -->
                    <div style="margin-top: 40px;">
                        <h3 style="color: #007cba; font-size: 22px; border-bottom: 3px solid #007cba; padding-bottom: 12px; margin-bottom: 25px;">Comparaison des Fonctionnalites</h3>
                        <table class="wp-list-table widefat fixed striped" style="margin-top: 15px; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <thead style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white;">
                                <tr>
                                    <th style="width: 40%; padding: 15px; font-weight: 700; text-align: left; border: none;">Fonctionnalite</th>
                                    <th style="width: 15%; text-align: center; padding: 15px; font-weight: 700; border: none;">Gratuit</th>
                                    <th style="width: 15%; text-align: center; padding: 15px; font-weight: 700; border: none;">Premium</th>
                                    <th style="width: 30%; padding: 15px; font-weight: 700; text-align: left; border: none;">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Templates de base</strong></td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>4 templates prédéfinis</td>
                                </tr>
                                <tr>
                                    <td><strong>Éléments standards</strong></td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Texte, image, ligne, rectangle</td>
                                </tr>
                                <tr>
                                    <td><strong>Intégration WooCommerce</strong></td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Variables de commande</td>
                                </tr>
                                <tr>
                                    <td><strong>Génération PDF</strong></td>
                                    <td style="text-align: center; color: #ffb900;">50/mois</td>
                                    <td style="text-align: center; color: #46b450;">✓ Illimitée</td>
                                    <td>Création de documents</td>
                                </tr>
                                <tr>
                                    <td><strong>Templates avancés</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Bibliothèque complète</td>
                                </tr>
                                <tr>
                                    <td><strong>Éléments premium</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Codes-barres, QR codes, graphiques</td>
                                </tr>
                                <tr>
                                    <td><strong>Génération en masse</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Création multiple de documents</td>
                                </tr>
                                <tr>
                                    <td><strong>API développeur</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Accès complet à l'API REST</td>
                                </tr>
                                <tr>
                                    <td><strong>White-label</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Rebranding complet</td>
                                </tr>
                                <tr>
                                    <td><strong>Support prioritaire</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>24/7 avec SLA garanti</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Section Notifications par Email -->
                    <div style="background: linear-gradient(135deg, #e7f5ff 0%, #f0f9ff 100%); border: none; border-radius: 12px; padding: 30px; margin-top: 30px; color: #343a40; box-shadow: 0 4px 12px rgba(0,102,204,0.15);">
                        <h3 style="margin-top: 0; color: #003d7a; font-size: 20px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                            📧 Notifications par Email
                        </h3>

                        <p style="color: #003d7a; margin: 0 0 25px 0; line-height: 1.6; font-size: 14px;">
                            Recevez une notification par email quand votre licence expire bientôt. C'est une excellente façon de ne jamais oublier de renouveler votre licence.
                        </p>

                        <form method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; align-items: start;">
                            <?php wp_nonce_field('pdf_builder_license', 'pdf_builder_license_nonce'); ?>
                            <input type="hidden" name="pdf_builder_save_notifications" value="1">

                            <!-- Toggle Notifications -->
                            <div style="background: rgba(255,255,255,0.6); padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; font-weight: 600; color: #003d7a;">
                                    <input type="checkbox" name="enable_expiration_notifications" value="1" <?php checked($enable_expiration_notifications, 1); ?> style="width: 20px; height: 20px; cursor: pointer; margin-top: 2px; accent-color: #0066cc; flex-shrink: 0;">
                                    <span style="line-height: 1.4;">
                                        Activer les notifications d'expiration<br>
                                        <span style="font-weight: 400; color: #666; font-size: 12px; display: block; margin-top: 6px;">
                                            ✓ 30 jours avant l'expiration<br>
                                            ✓ 7 jours avant l'expiration
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <!-- Email Input -->
                            <div style="background: rgba(255,255,255,0.6); padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <label for="notification_email" style="display: block; font-weight: 600; color: #003d7a; margin-bottom: 10px; font-size: 14px;">
                                    Email pour les notifications :
                                </label>
                                <input type="email" name="notification_email" id="notification_email" value="<?php echo esc_attr($notification_email); ?>"
                                    placeholder="admin@example.com"
                                    style="width: 100%; padding: 10px 12px; border: 2px solid #0066cc; border-radius: 6px; font-size: 13px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                                    onfocus="this.style.borderColor='#003d7a'; this.style.boxShadow='0 0 0 3px rgba(0,102,204,0.1)';"
                                    onblur="this.style.borderColor='#0066cc'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                                <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;">
                                    Défaut : adresse administrateur du site
                                </p>
                            </div>

            </div>
            </form>
        </div>

        <div id="performance" class="tab-content hidden-tab">
         <form method="post" id="performance-form" action="">
                <?php wp_nonce_field('pdf_builder_performance_settings', 'pdf_builder_performance_nonce'); ?>
                <input type="hidden" name="current_tab" value="performance">
                <input type="hidden" name="submit_performance" value="1">
                <h3 class="section-title">Paramètres de Performance</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="auto_save_enabled">Sauvegarde Auto</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="auto_save_enabled" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="auto_save_enabled" name="auto_save_enabled" value="1"
                                       <?php checked($canvas_settings['auto_save_enabled'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Sauvegarde automatique</span>
                        </div>
                        <div class="toggle-description">Sauvegarde automatique pendant l'édition</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_save_interval">Intervalle Auto-save (secondes)</label></th>
                    <td>
                        <input type="number" id="auto_save_interval" name="auto_save_interval" value="<?php echo intval($canvas_settings['auto_save_interval'] ?? 30); ?>"
                               min="10" max="300" step="10" <?php echo (!($canvas_settings['auto_save_enabled'] ?? false)) ? 'disabled' : ''; ?> />
                        <p class="description">Intervalle entre chaque sauvegarde automatique</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="compress_images">Compresser les Images</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="compress_images" name="compress_images" value="1"
                                       <?php checked($settings['compress_images'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Compression d'images</span>
                        </div>
                        <div class="toggle-description">Compresse les images pour réduire la taille des PDFs</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="image_quality">Qualité des Images (%)</label></th>
                    <td>
                        <input type="range" id="image_quality" name="image_quality" value="<?php echo intval($settings['image_quality'] ?? 85); ?>"
                               min="30" max="100" step="5" style="width: 300px;" oninput="document.getElementById('image_quality_value').textContent = this.value + '%';" />
                        <span id="image_quality_value" style="margin-left: 10px; font-weight: bold;">
                            <?php echo intval($settings['image_quality'] ?? 85); ?>%
                        </span>
                        <p class="description">Plus faible = fichiers plus petits mais moins de détails</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="optimize_for_web">Optimiser pour le Web</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="optimize_for_web" name="optimize_for_web" value="1"
                                       <?php checked($settings['optimize_for_web'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Optimisation web</span>
                        </div>
                        <div class="toggle-description">Réduit la taille du fichier pour une meilleure distribution web</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_hardware_acceleration">Accélération Matérielle</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_hardware_acceleration" name="enable_hardware_acceleration" value="1"
                                       <?php checked($settings['enable_hardware_acceleration'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">GPU activé</span>
                        </div>
                        <div class="toggle-description">Utilise les ressources GPU si disponibles</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="limit_fps">Limiter les FPS</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="limit_fps" name="limit_fps" value="1"
                                       <?php checked($settings['limit_fps'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Limitation FPS</span>
                        </div>
                        <div class="toggle-description">Limite le rendu pour économiser les ressources</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_fps">FPS Maximum</label></th>
                    <td>
                        <input type="number" id="max_fps" name="max_fps" value="<?php echo intval($settings['max_fps'] ?? 60); ?>"
                               min="15" max="240" <?php echo !($settings['limit_fps'] ?? false) ? 'disabled' : ''; ?> />
                        <p class="description">Images par seconde maximales (15-240 FPS)</p> 
                    </td>
                </tr>
            </table>

            <!-- Section Nettoyage -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3 class="section-title">Nettoyage & Maintenance</h3>
                <p>Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.</p>

                <button type="button" id="clear-cache-btn" class="button button-secondary">
                    🗑️ Vider le Cache
                </button>

                <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px;">
                    <p style="margin: 0;"><strong>💡 Conseil :</strong> Videz le cache si vous rencontrez des problèmes de génération PDF ou si les changements n'apparaissent pas.</p>
                </div>
            </div>

            <p class="submit">
                <button type="submit" name="submit_performance" class="button button-primary" id="performance-submit-btn">Enregistrer les paramètres de performance</button>
            </p>
         </form>
        </div>

        <div id="pdf" class="tab-content hidden-tab">
            <form method="post" id="pdf-form" action="">
                <?php wp_nonce_field('pdf_builder_pdf_settings', 'pdf_builder_pdf_nonce'); ?>
                <input type="hidden" name="current_tab" value="pdf">
                <input type="hidden" name="submit_pdf" value="1">
                <h2>Paramètres PDF</h2>

            <h3 class="section-title">Qualité & Export</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="export_quality">Qualité d'Export</label></th>
                    <td>
                        <select id="export_quality" name="export_quality">
                            <option value="screen" <?php selected($settings['export_quality'] ?? 'print', 'screen'); ?>>Écran (72 DPI)</option>
                            <option value="print" <?php selected($settings['export_quality'] ?? 'print', 'print'); ?>>Impression (300 DPI)</option>
                            <option value="prepress" <?php selected($settings['export_quality'] ?? 'print', 'prepress'); ?>>Pré-presse (600 DPI)</option>
                        </select>
                        <p class="description">Définit la résolution de sortie du PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="export_format">Format d'Export</label></th>
                    <td>
                        <select id="export_format" name="export_format">
                            <option value="pdf" <?php selected($settings['export_format'] ?? 'pdf', 'pdf'); ?>>PDF</option>
                            <option value="png" <?php selected($settings['export_format'] ?? 'pdf', 'png'); ?>>PNG</option>
                            <option value="jpg" <?php selected($settings['export_format'] ?? 'pdf', 'jpg'); ?>>JPEG</option>
                        </select>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Métadonnées & Contenu</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pdf_author">Auteur du PDF</label></th>
                    <td>
                        <input type="text" id="pdf_author" name="pdf_author" value="<?php echo esc_attr($settings['pdf_author'] ?? get_bloginfo('name')); ?>"
                               class="regular-text" />
                        <p class="description">Sera inclus dans les propriétés du PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pdf_subject">Sujet du PDF</label></th>
                    <td>
                        <input type="text" id="pdf_subject" name="pdf_subject" value="<?php echo esc_attr($settings['pdf_subject'] ?? ''); ?>"
                               class="regular-text" placeholder="Ex: Facture, Devis, etc." />
                        <p class="description">Sujet dans les propriétés du PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="include_metadata">Inclure les Métadonnées</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="include_metadata" name="include_metadata" value="1"
                                       <?php checked($settings['include_metadata'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Métadonnées PDF</span>
                        </div>
                        <div class="toggle-description">Ajoute les données de titre, auteur, date, etc.</div>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Optimisation & Compression</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="embed_fonts">Intégrer les Polices</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="embed_fonts" name="embed_fonts" value="1"
                                       <?php checked($settings['embed_fonts'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Polices intégrées</span>
                        </div>
                        <div class="toggle-description">Inclut les polices personnalisées dans le PDF (fichiers plus gros)</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_crop">Recadrage Automatique</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="auto_crop" name="auto_crop" value="1"
                                       <?php checked($settings['auto_crop'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Recadrage auto</span>
                        </div>
                        <div class="toggle-description">Supprime les marges blanches automatiquement</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_image_size">Taille Max des Images (px)</label></th>
                    <td>
                        <input type="number" id="max_image_size" name="max_image_size" value="<?php echo intval($settings['max_image_size'] ?? 2048); ?>"
                               min="512" max="8192" step="256" />
                        <p class="description">Les images plus grandes seront redimensionnées</p>
                    </td>
                </tr>
            </table>

            <!-- Aide & Conseils -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3>💡 Conseils d'Optimisation</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Pour impression :</strong> Utilisez la qualité "Haute" + Pré-presse + Polices intégrées</li>
                    <li><strong>Pour web :</strong> Utilisez la qualité "Moyenne" + Écran + Compression images</li>
                    <li><strong>Pour email :</strong> Utilisez la qualité "Basse" + Optimiser pour le web + Recadrage auto</li>
                </ul>
            </div>

            <p class="submit">
                <button type="submit" name="submit_pdf" class="button button-primary">Enregistrer les paramètres PDF</button>
            </p>
            </form>
        </div>

        <div id="securite" class="tab-content hidden-tab">
            <h2>Paramètres de Sécurité</h2>
            <p style="color: #666;">Configurations de sécurité et limites système. Pour le debug et logging, voir l'onglet Développeur.</p>

         <form method="post" id="securite-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_securite_nonce'); ?>
                <input type="hidden" name="submit_security" value="1">

            <h3 class="section-title">⚙️ Limites & Protections Système</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="max_template_size">Taille Max Template (octets)</label></th>
                    <td>
                        <input type="number" id="max_template_size" name="max_template_size"
                               value="<?php echo intval($settings['max_template_size'] ?? 52428800); ?>" min="1048576" step="1048576" />
                        <p class="description">Maximum: ~<?php echo number_format(intval($settings['max_template_size'] ?? 52428800) / 1048576); ?> MB (défaut: 50 MB)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">Temps Max d'Exécution (secondes)</label></th>
                    <td>
                        <input type="number" id="max_execution_time" name="max_execution_time"
                               value="<?php echo intval($settings['max_execution_time'] ?? 300); ?>" min="1" max="3600"/>
                        <p class="description">Temps avant timeout pour la génération PDF (défaut: 300 secondes)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="memory_limit">Limite Mémoire</label></th>
                    <td>
                        <input type="text" id="memory_limit" name="memory_limit"
                               value="<?php echo esc_attr($settings['memory_limit'] ?? '256M'); ?>"
                               placeholder="256M" />
                        <p class="description">Format: 256M, 512M, 1G. Doit être ≥ taille max template (défaut: 256M)</p>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">🔐 Protections</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Nonces WordPress</label></th>
                    <td>
                        <p style="margin: 0;">✓ Les nonces expirent après <strong>24 heures</strong> pour plus de sécurité</p>
                        <p style="margin: 0; margin-top: 10px;">✓ Tous les formulaires sont protégés par des nonces WordPress</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Rate Limiting</label></th>
                    <td>
                        <p style="margin: 0;">✓ Le rate limiting est automatiquement activé pour prévenir les abus</p>
                        <p style="margin: 0; margin-top: 10px;">Limite: <strong>100 requêtes par minute</strong> par IP</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Permissions</label></th>
                    <td>
                        <p style="margin: 0;">✓ Accès à PDF Builder Pro limité aux rôles autorisés</p>
                        <p style="margin: 0; margin-top: 10px;">Voir l'onglet "Rôles" pour configurer les accès</p>
                    </td>
                </tr>
            </table>

            <!-- Section Sécurité avancée -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3>🔒 Sécurité Avancée</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>✓ Sanitization de toutes les entrées utilisateur</li>
                    <li>✓ Validation des fichiers uploadés</li>
                    <li>✓ Protection XSS et CSRF</li>
                    <li>✓ Permissions WordPress vérifiées</li>
                    <li>✓ Logs sécurisés des actions critiques</li>
                </ul>
            </div>

            <!-- Conseils de sécurité -->
            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #856404;">💡 Conseils Sécurité</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li><strong>Production :</strong> Désactivez le mode debug et mettez "Error" en log level</li>
                    <li><strong>Memory limit :</strong> Doit être suffisant pour vos plus gros PDFs</li>
                    <li><strong>Mises à jour :</strong> Gardez WordPress et les plugins à jour</li>
                    <li><strong>Sauvegardes :</strong> Effectuez des sauvegardes régulières</li>
                </ul>
            </div>

            <p class="submit">
                <button type="submit" name="submit_security" class="button button-primary">Enregistrer les paramètres de sécurité</button>
            </p>
         </form>
        </div>

        <div id="roles" class="tab-content hidden-tab">
            <h2>Gestion des Rôles et Permissions</h2>

            <!-- Message de confirmation que l'onglet est chargé -->
            <div style="margin-bottom: 20px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                ✅ Onglet Rôles chargé - Bouton de sauvegarde visible ci-dessous
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

                <!-- Bouton de sauvegarde en haut -->
                <div style="margin-bottom: 20px; padding: 15px; background: #e7f3ff; border: 1px solid #b3d7ff; border-radius: 8px;">
                    <p class="submit" style="margin: 0;">
                        <button type="submit" name="submit_roles" class="button button-primary" style="font-size: 14px; padding: 8px 16px;">
                            💾 Sauvegarder les Rôles
                        </button>
                        <span class="description" style="margin-left: 15px; color: #0056b3;">
                            Cliquez ici pour enregistrer vos modifications
                        </span>
                    </p>
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

                    input:checked + .toggle-slider:hover {
                        box-shadow: 0 0 8px rgba(34, 113, 177, 0.5);
                    }
                </style>

                <!-- Bouton de sauvegarde en bas aussi -->
                <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border: 1px solid #b3d7ff; border-radius: 8px;">
                    <p class="submit" style="margin: 0;">
                        <button type="submit" name="submit_roles" class="button button-primary" style="font-size: 14px; padding: 8px 16px;">
                            💾 Sauvegarder les Rôles (Bas de page)
                        </button>
                        <span class="description" style="margin-left: 15px; color: #0056b3;">
                            Cliquez ici pour enregistrer vos modifications
                        </span>
                    </p>
                </div>

            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Empêcher l'interférence AJAX avec le formulaire des rôles
                    const rolesForm = document.querySelector('#roles form');
                    if (rolesForm) {
                        // Log pour déboguer


                        rolesForm.addEventListener('submit', function(e) {


                            // Laisser le formulaire se soumettre normalement (POST)

                        });

                        // Empêcher tout autre event listener AJAX
                        rolesForm.addEventListener('click', function(e) {
                            if (e.target.type === 'submit') {

                            }
                        }, true); // useCapture = true
                    } else {
                        console.error('❌ Roles form not found!');
                    }

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

                            roleToggles.forEach(function(checkbox) {
                                if (!checkbox.disabled) {
                                    checkbox.checked = true;
                                }
                            });
                            updateSelectedCount();
                        });
                    }

                    // Bouton Rôles Courants
                    if (selectCommonBtn) {
                        selectCommonBtn.addEventListener('click', function() {

                            const commonRoles = ['administrator', 'editor', 'shop_manager'];
                            roleToggles.forEach(function(checkbox) {
                                const isCommon = commonRoles.includes(checkbox.value);
                                if (!checkbox.disabled) {
                                    checkbox.checked = isCommon;
                                }
                            });
                            updateSelectedCount();
                        });
                    }

                    // Bouton Désélectionner Tout
                    if (selectNoneBtn) {
                        selectNoneBtn.addEventListener('click', function() {

                            roleToggles.forEach(function(checkbox) {
                                if (!checkbox.disabled) {
                                    checkbox.checked = false;
                                }
                            });
                            updateSelectedCount();
                        });
                    }

                    // Mettre à jour le compteur quand un toggle change
                    roleToggles.forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {

                            updateSelectedCount();
                        });
                    });

                    // Initialiser le compteur
                    updateSelectedCount();

                });
            </script>

            <!-- Permissions incluses -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #003d66;">🔐 Permissions Incluses</h3>
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
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 20px;">margin-top: 20px;">
                <h3 style="margin-top: 0; color: #856404;">⚠️ Informations Importantes</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li>Les rôles non sélectionnés n'auront aucun accès à PDF Builder Pro</li>
                    <li>Le rôle "Administrator" a toujours accès complet, indépendamment</li>
                    <li>Minimum requis : au moins un rôle sélectionné</li>
                </ul>
            </div>

            <!-- Conseils d'utilisation -->
            <div style="background: #f0f0f0; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0;">💡 Conseils d'Utilisation</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Basique :</strong> Sélectionnez "Administrator" et "Editor"</li>
                    <li><strong>WooCommerce :</strong> Ajoutez "Shop Manager"</li>
                    <li><strong>Multi-utilisateurs :</strong> Utilisez "Rôles Courants" pour configuration rapide</li>
                    <li><strong>Sécurité :</strong> Limitez l'accès aux rôles les moins permissifs nécessaires</li>
                </ul>
            </div>

            <!-- Tableau de référence des rôles -->
            <div style="margin-top: 30px;">
                <h3>📋 Référence des Rôles WordPress</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Rôle</th>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 30%; text-align: center;">Recommandé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Administrator</strong></td>
                            <td>Accès complet à toutes les fonctionnalités WordPress et PDF Builder Pro</td>
                            <td style="text-align: center; color: #46b450;">✓ Oui</td>
                        </tr>
                        <tr>
                            <td><strong>Editor</strong></td>
                            <td>Peut publier et gérer tous les articles, y compris les PDFs</td>
                            <td style="text-align: center; color: #46b450;">✓ Oui</td>
                        </tr>
                        <tr>
                            <td><strong>Author</strong></td>
                            <td>Peut publier ses propres articles avec générateur PDF</td>
                            <td style="text-align: center;">○ Optionnel</td>
                        </tr>
                        <tr>
                            <td><strong>Contributor</strong></td>
                            <td>Peut soumettre des brouillons mais n'a accès qu'à la prévisualisation</td>
                            <td style="text-align: center;">○ Optionnel</td>
                        </tr>
                        <tr>
                            <td><strong>Shop Manager</strong></td>
                            <td>Gestionnaire WooCommerce, accès aux factures et devis PDF</td>
                            <td style="text-align: center; color: #46b450;">✓ Pour boutiques</td>
                        </tr>
                        <tr>
                            <td><strong>Customer</strong></td>
                            <td>Client WooCommerce, accès à ses commandes</td>
                            <td style="text-align: center; color: #dc3232;">✗ Non</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="notifications" class="tab-content hidden-tab">
            <h2>Paramètres de Notifications</h2>

            <form method="post" id="notifications-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_notifications_nonce'); ?>
                <input type="hidden" name="submit_notifications" value="1">

            <?php
            // Traitement de la sauvegarde des notifications
            if (isset($_POST['submit_notifications']) && isset($_POST['pdf_builder_notifications_nonce'])) {

                if (wp_verify_nonce($_POST['pdf_builder_notifications_nonce'], 'pdf_builder_settings')) {
                    $notification_settings = [
                        'email_notifications_enabled' => isset($_POST['email_notifications_enabled']),
                        'admin_email' => sanitize_email($_POST['admin_email'] ?? get_option('admin_email')),
                        'notification_log_level' => sanitize_text_field($_POST['notification_log_level'] ?? 'error'),
                        'notification_on_generation' => isset($_POST['notification_on_generation']),
                        'notification_on_error' => isset($_POST['notification_on_error']),
                        'notification_on_deletion' => isset($_POST['notification_on_deletion']),
                    ];
                    foreach ($notification_settings as $key => $value) {
                        update_option('pdf_builder_' . $key, $value);
                    }

                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de notifications sauvegardés.</p></div>';
                }
            }

            $email_notifications = get_option('pdf_builder_email_notifications_enabled', false);
            $admin_email = get_option('pdf_builder_admin_email', get_option('admin_email'));
            $notification_level = get_option('pdf_builder_notification_log_level', 'error');
            ?>

            <h3 class="section-title">Notifications par Email</h3>

            <table class="form-table">
                    <tr>
                        <th scope="row"><label for="email_notifications_enabled">Notifications Email</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="email_notifications_enabled" name="email_notifications_enabled" value="1"
                                           <?php checked($email_notifications); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Notifications email</span>
                            </div>
                            <div class="toggle-description">Active les notifications par email pour les erreurs et événements importants</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="admin_email">Email Administrateur</label></th>
                        <td>
                            <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>"
                                   class="regular-text" autocomplete="email" />
                            <p class="description">Adresse email pour recevoir les notifications système</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_log_level">Niveau de Notification</label></th>
                        <td>
                            <select id="notification_log_level" name="notification_log_level">
                                <option value="error" <?php selected($notification_level, 'error'); ?>>Erreurs uniquement</option>
                                <option value="warning" <?php selected($notification_level, 'warning'); ?>>Erreurs et avertissements</option>
                                <option value="info" <?php selected($notification_level, 'info'); ?>>Tous les événements importants</option>
                            </select>
                            <p class="description">Détermine quels événements déclencheront une notification email</p>
                        </td>
                    </tr>
            </table>

                <h3 class="section-title">Événements de Notification</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="notification_on_generation">Génération PDF</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notification_on_generation" name="notification_on_generation" value="1"
                                           <?php checked(get_option('pdf_builder_notification_on_generation')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Génération réussie</span>
                            </div>
                            <div class="toggle-description">Notifier à chaque génération de PDF réussie</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_on_error">Erreurs</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notification_on_error" name="notification_on_error" value="1"
                                           <?php checked(get_option('pdf_builder_notification_on_error')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Erreurs de génération</span>
                            </div>
                            <div class="toggle-description">Notifier en cas d'erreur lors de la génération</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_on_deletion">Suppression</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notification_on_deletion" name="notification_on_deletion" value="1"
                                           <?php checked(get_option('pdf_builder_notification_on_deletion')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Suppression templates</span>
                            </div>
                            <div class="toggle-description">Notifier lors de la suppression de templates</div>
                        </td>
                    </tr>
                </table>

                <h3 class="section-title">Configuration SMTP</h3>
                <p class="description" style="margin-bottom: 15px;">Configurez un serveur SMTP pour l'envoi des notifications par email</p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="smtp_enabled">Activer SMTP</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="smtp_enabled" name="smtp_enabled" value="1"
                                           <?php checked(get_option('pdf_builder_smtp_enabled')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Utiliser SMTP</span>
                            </div>
                            <div class="toggle-description">Active l'envoi d'emails via serveur SMTP au lieu de la fonction mail() par défaut</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_host">Serveur SMTP</label></th>
                        <td>
                            <input type="text" id="smtp_host" name="smtp_host"
                                   value="<?php echo esc_attr(get_option('pdf_builder_smtp_host', 'smtp.gmail.com')); ?>"
                                   class="regular-text" placeholder="smtp.gmail.com" />
                            <p class="description">Adresse du serveur SMTP (ex: smtp.gmail.com, smtp.mailgun.org)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_port">Port SMTP</label></th>
                        <td>
                            <input type="number" id="smtp_port" name="smtp_port"
                                   value="<?php echo intval(get_option('pdf_builder_smtp_port', 587)); ?>"
                                   min="1" max="65535" class="small-text" />
                            <p class="description">Port du serveur SMTP (587 pour TLS, 465 pour SSL, 25 pour non-chiffré)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_encryption">Chiffrement</label></th>
                        <td>
                            <select id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" <?php selected(get_option('pdf_builder_smtp_encryption', 'tls'), 'tls'); ?>>TLS</option>
                                <option value="ssl" <?php selected(get_option('pdf_builder_smtp_encryption', 'tls'), 'ssl'); ?>>SSL</option>
                                <option value="none" <?php selected(get_option('pdf_builder_smtp_encryption', 'tls'), 'none'); ?>>Aucun</option>
                            </select>
                            <p class="description">Type de chiffrement pour la connexion SMTP</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_auth">Authentification</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="smtp_auth" name="smtp_auth" value="1"
                                           <?php checked(get_option('pdf_builder_smtp_auth', true)); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Authentification requise</span>
                            </div>
                            <div class="toggle-description">La plupart des serveurs SMTP nécessitent une authentification</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_username">Nom d'utilisateur</label></th>
                        <td>
                            <input type="text" id="smtp_username" name="smtp_username"
                                   value="<?php echo esc_attr(get_option('pdf_builder_smtp_username')); ?>"
                                   class="regular-text" placeholder="votre-email@gmail.com" autocomplete="username" />
                            <p class="description">Nom d'utilisateur pour l'authentification SMTP</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_password">Mot de passe</label></th>
                        <td>
                            <input type="password" id="smtp_password" name="smtp_password"
                                   value="<?php echo esc_attr(get_option('pdf_builder_smtp_password')); ?>"
                                   class="regular-text" placeholder="••••••••" autocomplete="current-password" />
                            <p class="description">Mot de passe pour l'authentification SMTP</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_from_email">Email expéditeur</label></th>
                        <td>
                            <input type="email" id="smtp_from_email" name="smtp_from_email"
                                   value="<?php echo esc_attr(get_option('pdf_builder_smtp_from_email', get_option('admin_email'))); ?>"
                                   class="regular-text" autocomplete="email" />
                            <p class="description">Adresse email utilisée comme expéditeur (From)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smtp_from_name">Nom expéditeur</label></th>
                        <td>
                            <input type="text" id="smtp_from_name" name="smtp_from_name"
                                   value="<?php echo esc_attr(get_option('pdf_builder_smtp_from_name', get_bloginfo('name'))); ?>"
                                   class="regular-text" autocomplete="name" />
                            <p class="description">Nom affiché comme expéditeur</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="button" id="test-smtp-connection" class="button button-secondary">
                        🔗 Tester la Connexion SMTP
                    </button>
                    <button type="button" id="test-notifications" class="button button-secondary">
                        🧪 Tester les Notifications
                    </button>
                </p>

            <!-- Informations sur les notifications -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #003d66;">📧 Informations sur les Notifications</h3>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li><strong>Email actuel :</strong> <?php echo esc_html($admin_email); ?></li>
                    <li>Les notifications sont envoyées aux administrateurs autorisés</li>
                    <li>Les emails peuvent être personnalisés via des filtres WordPress</li>
                    <li>Les logs de notification sont conservés pendant 30 jours</li>
                </ul>
            </div>

            <!-- Exemples de notifications -->
            <div style="background: #f8f9fa; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top:20px;">
                <h3 style="margin-top: 0;">💡 Exemples de Notifications</h3>
                <p><strong>Erreur :</strong> "PDF generation failed for order #1234: Memory limit exceeded"</p>
                <p><strong>Avertissement :</strong> "Large template detected: file size 45MB, consider optimizing"</p>
                <p><strong>Info :</strong> "Successfully generated 150 PDFs in batch process (12.5s)"</p>
            </div>

            <!-- Tableau des types de notifications -->
            <div style="margin-top: 30px;">
                <h3>📋 Types de Notifications</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Type</th>
                            <th style="width: 35%;">Description</th>
                            <th style="width: 20%; text-align: center;">Niveau</th>
                            <th style="width: 20%; text-align: center;">Activé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Génération Réussie</strong></td>
                            <td>Un PDF a été généré avec succès</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled <?php checked(get_option('pdf_builder_notification_on_generation')); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Erreur</strong></td>
                            <td>Une erreur s'est produite lors de la génération</td>
                            <td style="text-align: center; color: #dc3232;">Erreur</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled <?php checked(get_option('pdf_builder_notification_on_error')); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Avertissement</strong></td>
                            <td>Dépassement de limite de ressources</td>
                            <td style="text-align: center; color: #ffb900;">Avertissement</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled checked />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Suppression</strong></td>
                            <td>Un template a été supprimé</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled <?php checked(get_option('pdf_builder_notification_on_deletion')); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Maintenance</strong></td>
                            <td>Mises à jour et maintenance du système</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled checked />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Activation License</strong></td>
                            <td>Licence activée ou expirée</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled checked />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="submit">
                <button type="submit" name="submit_notifications" class="button button-primary">Enregistrer les paramètres de notifications</button>
            </p>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const emailNotificationsToggle = document.getElementById('email_notifications_enabled');
                const dependentToggles = [
                    'notification_on_generation',
                    'notification_on_error',
                    'notification_on_deletion',
                    'smtp_enabled',
                    'smtp_auth'
                ];
                const dependentFields = [
                    'admin_email',
                    'notification_log_level',
                    'smtp_host',
                    'smtp_port',
                    'smtp_encryption',
                    'smtp_username',
                    'smtp_password',
                    'smtp_from_email',
                    'smtp_from_name'
                ];

                function updateDependentControls() {
                    const isEnabled = emailNotificationsToggle.checked;

                    // Désactiver/activer les toggles dépendants
                    dependentToggles.forEach(function(toggleId) {
                        const toggle = document.getElementById(toggleId);
                        if (toggle) {
                            toggle.disabled = !isEnabled;
                            if (!isEnabled) {
                                toggle.checked = false;
                            }
                        }
                    });

                    // Désactiver/activer les champs de texte dépendants
                    dependentFields.forEach(function(fieldId) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.disabled = !isEnabled;
                        }
                    });

                    // Désactiver/activer les boutons de test
                    const testButtons = ['test-smtp-connection', 'test-notifications'];
                    testButtons.forEach(function(buttonId) {
                        const button = document.getElementById(buttonId);
                        if (button) {
                            button.disabled = !isEnabled;
                        }
                    });
                }

                // Appliquer l'état initial
                updateDependentControls();

                // Écouter les changements
                emailNotificationsToggle.addEventListener('change', updateDependentControls);
            });
            </script>
            </form>
        </div>

        <div id="canvas" class="tab-content hidden-tab">
            <h2>Paramètres Canvas</h2>

         <form method="post" id="canvas-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_canvas_nonce'); ?>
                <input type="hidden" name="submit_canvas" value="1">

            <?php
            // Récupérer les paramètres canvas depuis les options WordPress
            $canvas_settings = get_option('pdf_builder_canvas_settings', []);
          // Définir les valeurs par défaut pour éviter les erreurs "Undefined array key"
            $canvas_settings = array_merge([
                'default_canvas_format' => 'A4',
                'default_canvas_orientation' => 'portrait',
                'default_canvas_unit' => 'px',
                'default_orientation' => 'portrait',
                'canvas_background_color' => '#ffffff',
                'canvas_show_transparency' => false,
                'container_background_color' => '#f8f9fa',
                'container_show_transparency' => false,
                'margin_top' => 28,
                'margin_right' => 28,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'show_margins' => false,
                'show_grid' => false,
                'grid_size' => 10,
                'grid_color' => '#e0e0e0',
                'snap_to_grid' => false,
                'snap_to_elements' => false,
                'snap_tolerance' => 5,
                'show_guides' => false,
                'default_zoom' => 100,
                'zoom_step' => 25,
                'min_zoom' => 10,
                'max_zoom' => 500,
                'zoom_with_wheel' => false,
                'pan_with_mouse' => false,
                'show_resize_handles' => false,
                'handle_size' => 8,
                'handle_color' => '#007cba',
                'enable_rotation' => false,
                'rotation_step' => 15,
                'multi_select' => false,
                'copy_paste_enabled' => false,
                'export_quality' => 'print',
                'export_format' => 'pdf',
                'compress_images' => true,
                'image_quality' => 85,
                'max_image_size' => 2048,
                'include_metadata' => true,
                'pdf_author' => 'PDF Builder Pro',
                'pdf_subject' => '',
                'auto_crop' => false,
                'embed_fonts' => true,
                'optimize_for_web' => true,
                'enable_hardware_acceleration' => true,
                'limit_fps' => true,
                'max_fps' => 60,
                'auto_save_enabled' => true,
                'auto_save_interval' => 30,
                'auto_save_versions' => 10,
                'undo_levels' => 50,
                'redo_levels' => 50,
                'enable_keyboard_shortcuts' => true,
                'debug_mode' => false,
                'show_fps' => false
            ], $canvas_settings);
            ?>

            <h3 class="section-title">Dimensions par Défaut</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_canvas_format">Format Canvas par défaut</label></th>
                    <td>
                        <select id="default_canvas_format" name="default_canvas_format">
                            <option value="A6" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A6'); ?>>A6</option>
                            <option value="A5" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A5'); ?>>A5</option>
                            <option value="A4" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A4'); ?>>A4</option>
                            <option value="A3" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A3'); ?>>A3</option>
                            <option value="A2" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A2'); ?>>A2</option>
                            <option value="A1" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A1'); ?>>A1</option>
                            <option value="A0" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'A0'); ?>>A0</option>
                            <option value="Letter" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'Letter'); ?>>Letter</option>
                            <option value="Legal" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'Legal'); ?>>Legal</option>
                            <option value="Tabloid" <?php selected($canvas_settings['default_canvas_format'] ?? 'A4', 'Tabloid'); ?>>Tabloid</option>
                        </select>
                        <p class="description">Format par défaut du canvas</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_canvas_orientation">Orientation Canvas par défaut</label></th>
                    <td>
                        <select id="default_canvas_orientation" name="default_canvas_orientation">
                            <option value="portrait" <?php selected($canvas_settings['default_canvas_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                            <option value="landscape" <?php selected($canvas_settings['default_canvas_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                        </select>
                        <p class="description">Orientation par défaut du canvas</p>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Fond & Couleurs</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="canvas_background_color">Couleur Fond Canvas</label></th>
                    <td>
                        <input type="color" id="canvas_background_color" name="canvas_background_color"
                               value="<?php echo esc_attr($canvas_settings['canvas_background_color'] ?? '#ffffff'); ?>" />
                        <p class="description">Couleur de fond du canvas</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="container_background_color">Couleur Fond Conteneur</label></th>
                    <td>
                        <input type="color" id="container_background_color" name="container_background_color"
                               value="<?php echo esc_attr($canvas_settings['container_background_color'] ?? '#f8f9fa'); ?>" />
                        <p class="description">Couleur de fond autour du canvas</p>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Marges</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_margins">Activer les marges</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="show_margins" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_margins" name="show_margins" value="1"
                                       <?php checked($canvas_settings['show_margins']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Marges activées</span>
                        </div>
                        <div class="toggle-description">Active/désactive l'affichage des lignes de marge dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Marges (mm)</label></th>
                    <td>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div>
                                <label for="margin_top" class="margin-label">Haut :</label>
                                <input type="number" id="margin_top" name="margin_top" class="margin-input"
                                       value="<?php echo intval($canvas_settings['margin_top'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_right" class="margin-label">Droite :</label>
                                <input type="number" id="margin_right" name="margin_right" class="margin-input"
                                       value="<?php echo intval($canvas_settings['margin_right'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_bottom" class="margin-label">Bas :</label>
                                <input type="number" id="margin_bottom" name="margin_bottom" class="margin-input"
                                       value="<?php echo intval($canvas_settings['margin_bottom'] ?? 10); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_left" class="margin-label">Gauche :</label>
                                <input type="number" id="margin_left" name="margin_left" class="margin-input"
                                       value="<?php echo intval($canvas_settings['margin_left'] ?? 10); ?>" min="0" />
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Grille & Aimants</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_grid">Activer la grille</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="show_grid" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_grid" name="show_grid" value="1"
                                       <?php checked($canvas_settings['show_grid']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Grille activée</span>
                        </div>
                        <div class="toggle-description">Active/désactive l'affichage de la grille dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="grid_size" <?php if (!$canvas_settings['show_grid']) {
                        echo 'style="color: #999;"';
                                                           } ?>>Taille Grille (px)</label></th>
                    <td>
                        <input type="number" id="grid_size" name="grid_size"
                               value="<?php echo intval($canvas_settings['grid_size'] ?? 10); ?>" min="5" max="100"
                               <?php if (!$canvas_settings['show_grid']) {
                                    echo 'disabled style="background-color: #f0f0f0; color: #999;"';
                               } ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="grid_color" <?php if (!$canvas_settings['show_grid']) {
                        echo 'style="color: #999;"';
                                                            } ?>>Couleur Grille</label></th>
                    <td>
                        <input type="color" id="grid_color" name="grid_color"
                               value="<?php echo esc_attr($canvas_settings['grid_color'] ?? '#e0e0e0'); ?>"
                               <?php if (!$canvas_settings['show_grid']) {
                                    echo 'disabled style="opacity: 0.6;"';
                               } ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_to_grid">Activer magnétisme grille</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="snap_to_grid" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="snap_to_grid" name="snap_to_grid" value="1"
                                       <?php checked($canvas_settings['snap_to_grid']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Magnétisme grille activé</span>
                        </div>
                        <div class="toggle-description">Active/désactive l'accrochage automatique des éléments à la grille dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_to_elements">Activer magnétisme éléments</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="snap_to_elements" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="snap_to_elements" name="snap_to_elements" value="1"
                                       <?php checked($canvas_settings['snap_to_elements']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Magnétisme éléments activé</span>
                        </div>
                        <div class="toggle-description">Active/désactive l'accrochage automatique des éléments entre eux dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_tolerance">Tolérance Aimantation (px)</label></th>
                    <td>
                        <input type="number" id="snap_tolerance" name="snap_tolerance"
                               value="<?php echo intval($canvas_settings['snap_tolerance'] ?? 5); ?>" min="1" max="50" />
                        <p class="description">Distance avant accrochage magnétique</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="show_guides">Activer les guides</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="show_guides" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_guides" name="show_guides" value="1"
                                       <?php checked($canvas_settings['show_guides']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Guides activés</span>
                        </div>
                        <div class="toggle-description">Active/désactive l'affichage des guides de positionnement dans l'éditeur</div>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Zoom & Navigation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_zoom">Zoom par Défaut (%)</label></th>
                    <td>
                        <input type="number" id="default_zoom" name="default_zoom"
                               value="<?php echo intval($canvas_settings['default_zoom'] ?? 100); ?>" min="10" max="500" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="zoom_step">Pas du Zoom (%)</label></th>
                    <td>
                        <input type="number" id="zoom_step" name="zoom_step"
                               value="<?php echo intval($canvas_settings['zoom_step'] ?? 25); ?>" min="5" max="100" />
                        <p class="description">Incrément lors du zoom avant/arrière</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="min_zoom">Zoom Minimum (%)</label></th>
                    <td>
                        <input type="number" id="min_zoom" name="min_zoom"
                               value="<?php echo intval($canvas_settings['min_zoom'] ?? 10); ?>" min="1" max="100" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_zoom">Zoom Maximum (%)</label></th>
                    <td>
                        <input type="number" id="max_zoom" name="max_zoom"
                               value="<?php echo intval($canvas_settings['max_zoom'] ?? 500); ?>" min="100" max="2000" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="zoom_with_wheel">Activer zoom molette</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="zoom_with_wheel" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="zoom_with_wheel" name="zoom_with_wheel" value="1"
                                       <?php checked($canvas_settings['zoom_with_wheel']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Zoom molette activé</span>
                        </div>
                        <div class="toggle-description">Active/désactive le zoom avec la molette de la souris dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_with_mouse">Activer panoramique souris</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="pan_with_mouse" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="pan_with_mouse" name="pan_with_mouse" value="1"
                                       <?php checked($canvas_settings['pan_with_mouse']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Panoramique souris activé</span>
                        </div>
                        <div class="toggle-description">Active/désactive le déplacement du canvas en glissant avec la souris dans l'éditeur</div>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Sélection & Manipulation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_resize_handles">Activer les poignées</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="show_resize_handles" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_resize_handles" name="show_resize_handles" value="1"
                                       <?php checked($canvas_settings['show_resize_handles']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Poignées activées</span>
                        </div>
                        <div class="toggle-description">Active/désactive l'affichage des poignées de redimensionnement dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="handle_size">Taille Poignée (px)</label></th>
                    <td>
                        <input type="number" id="handle_size" name="handle_size"
                               value="<?php echo intval($canvas_settings['handle_size'] ?? 8); ?>" min="4" max="20" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_rotation">Rotation d'Éléments</label></th>
                    <td>
                        <div class="toggle-container">
                            <input type="hidden" name="enable_rotation" value="0" />
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_rotation" name="enable_rotation" value="1"
                                       <?php checked($canvas_settings['enable_rotation']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Rotation activée</span>
                        </div>
                        <div class="toggle-description">Permet la rotation des éléments</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="rotation_step">Pas Rotation (degrés)</label></th>
                    <td>
                        <input type="number" id="rotation_step" name="rotation_step"
                               value="<?php echo intval($canvas_settings['rotation_step'] ?? 15); ?>" min="1" max="90" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="multi_select">Activer sélection multiple</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="multi_select" name="multi_select" value="1"
                                       <?php checked($canvas_settings['multi_select']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Sélection multiple activée</span>
                        </div>
                        <div class="toggle-description">Active/désactive la possibilité de sélectionner plusieurs éléments dans l'éditeur</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="copy_paste_enabled">Activer copier/coller</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="copy_paste_enabled" name="copy_paste_enabled" value="1"
                                       <?php checked($canvas_settings['copy_paste_enabled']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Copier/coller activé</span>
                        </div>
                        <div class="toggle-description">Active/désactive les raccourcis Ctrl+C / Ctrl+V dans l'éditeur</div>
                    </td>
                </tr>
            </table>

            <h3 class="section-title">Undo/Redo & Auto-save</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="undo_levels">Niveaux Undo</label></th>
                    <td>
                        <input type="number" id="undo_levels" name="undo_levels"
                               value="<?php echo intval($canvas_settings['undo_levels'] ?? 50); ?>" min="1" max="500" />
                        <p class="description">Nombre d'actions à mémoriser pour annuler</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="redo_levels">Niveaux Redo</label></th>
                    <td>
                        <input type="number" id="redo_levels" name="redo_levels"
                               value="<?php echo intval($canvas_settings['redo_levels'] ?? 50); ?>" min="1" max="500" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_save_versions">Versions Auto-save</label></th>
                    <td>
                        <input type="number" id="auto_save_versions" name="auto_save_versions"
                               value="<?php echo intval($canvas_settings['auto_save_versions'] ?? 10); ?>" min="1" max="100" />
                        <p class="description">Nombre de versions à conserver</p>
                    </td>
                </tr>
            </table>

            <!-- Conseils Canvas -->
            <div style="background: #f8f9fa; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0;">💡 Conseils Canvas</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Performance :</strong> Réduisez la taille grille et les niveaux undo sur machines lentes</li>
                    <li><strong>Précision :</strong> Activez le magnétisme pour alignement automatique</li>
                    <li><strong>Navigation :</strong> Activez zoom molette et panoramique pour meilleure ergonomie</li>
                    <li><strong>Sécurité :</strong> Les versions auto-save permettent de récupérer en cas de crash</li>
                </ul>
            </div>

            <p class="submit">
                <button type="submit" name="submit_canvas" class="button button-primary">Enregistrer les paramètres Canvas</button>
            </p>
         </form>
        </div>

        <div id="templates" class="tab-content hidden-tab">
            <style>
                #templates #global-save-btn { display: none !important; }
            </style>
            <h2>Assignation des Templates</h2>

            <p style="margin-bottom: 20px;">Assignez automatiquement des templates aux différents statuts de commande WooCommerce.</p>

            <?php
            // Traitement de la sauvegarde
            if (isset($_POST['submit_templates']) && isset($_POST['pdf_builder_templates_nonce'])) {

                if (wp_verify_nonce($_POST['pdf_builder_templates_nonce'], 'pdf_builder_templates')) {
                    $template_mappings = [];
                    if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
                        foreach ($_POST['order_status_templates'] as $status => $template_id) {
                            $template_id = intval($template_id);
                            if ($template_id > 0) {
                                            $template_mappings[sanitize_text_field($status)] = $template_id;
                            }
                        }
                    }
                    update_option('pdf_builder_order_status_templates', $template_mappings);
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Templates assignés avec succès.</p></div>';
                }
            }

            // Récupérer les statuts de commande WooCommerce
            $order_statuses = [];
            if (function_exists('wc_get_order_statuses')) {
                $order_statuses = wc_get_order_statuses();
            } else {
            // Fallback : statuts standards
                $order_statuses = [
                    'wc-pending' => 'En attente',
                    'wc-processing' => 'En cours',
                    'wc-on-hold' => 'En attente de paiement',
                    'wc-completed' => 'Terminée',
                    'wc-cancelled' => 'Annulée',
                    'wc-refunded' => 'Remboursée',
                    'wc-failed' => 'Échec du paiement'
                ];
            }

            // Récupérer les mappings actuels
            $current_mappings = get_option('pdf_builder_order_status_templates', []);
         // Récupérer les templates disponibles
            $templates = get_posts([
                'post_type' => 'pdf_template',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            ?>

            <form method="post" id="templates-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_templates_nonce'); ?>
                <input type="hidden" name="submit_templates" value="1">

                <h3 class="section-title">Mappage des Statuts aux Templates</h3>

                <table class="form-table">
                    <?php foreach ($order_statuses as $status_key => $status_name) :
                        $display_status = str_replace('wc-', '', $status_key);
                        $selected_template = isset($current_mappings[$status_key]) ? $current_mappings[$status_key] : '';
                        ?>
                        <tr>
                            <th scope="row">
                                <label for="template_<?php echo esc_attr($display_status); ?>">
                                    <strong><?php echo esc_html($status_name); ?></strong><br>
                                    <code style="color: #666;"><?php echo esc_html($display_status); ?></code>
                                </label>
                            </th>
                            <td>
                                <select name="order_status_templates[<?php echo esc_attr($status_key); ?>]"
                                        id="template_<?php echo esc_attr($display_status); ?>" class="regular-text">
                                    <option value="">-- Utiliser le template par défaut --</option>
                                    <?php foreach ($templates as $template) :
                                        ?>
                                        <option value="<?php echo intval($template->ID); ?>"
                                                <?php selected($selected_template, $template->ID); ?>>
                                            <?php echo esc_html($template->post_title ?: '(Sans titre)'); ?>
                                        </option>
                                        <?php
                                    endforeach; ?>
                                </select>
                                <p class="description">
                                    Template automatique pour les commandes avec ce statut
                                </p>
                            </td>
                        </tr>
                        <?php
                    endforeach; ?>
                </table>

                <p class="submit">
                    <button type="submit" name="submit_templates" class="button button-primary">
                        Sauvegarder les Assignations
                    </button>
                </p>
            </form>

            <!-- Info WooCommerce -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #003d66;">📦 Intégration WooCommerce</h3>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li><strong>Statuts disponibles :</strong> <?php echo count($order_statuses); ?> statuts détectés</li>
                    <li><strong>Templates disponibles :</strong> <?php echo count($templates); ?> templates</li>
                    <li>Chaque statut de commande peut avoir son propre template</li>
                    <li>Les commandes utiliseront automatiquement le template assigné à leur statut</li>
                    <li>Laissez vide pour utiliser le template par défaut</li>
                </ul>
            </div>

            <!-- Tableau récapitulatif -->
            <div style="margin-top: 30px;">
                <h3>📋 Vue d'ensemble des Assignations</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Statut</th>
                            <th style="width: 50%;">Template Assigné</th>
                            <th style="width: 20%; text-align: center;">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_statuses as $status_key => $status_name) :
                            $template_id = isset($current_mappings[$status_key]) ? $current_mappings[$status_key] : false;
                            $template_name = $template_id ? get_the_title($template_id) : '(Défaut)';
                            $template_type = $template_id ? 'Personnalisé' : 'Défaut';
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($status_name); ?></strong></td>
                                <td><?php echo esc_html($template_name); ?></td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 3px; background: <?php echo $template_id ? '#d4edda' : '#e9ecef'; ?>; color: <?php echo $template_id ? '#155724' : '#666'; ?>;">
                                        <?php echo $template_type; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
                        endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Conseils d'utilisation -->
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #856404;">💡 Conseils d'Utilisation</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li><strong>Factures :</strong> Assignez un template "Facture" au statut "Terminée"</li>
                    <li><strong>Confirmations :</strong> Utilisez un template "Confirmation" pour le statut "En attente"</li>
                    <li><strong>Avis d'expédition :</strong> Assignez au statut "En cours"</li>
                    <li><strong>Avoirs :</strong> Créez un template "Avoir" pour les remboursements</li>
                    <li>Les templates peuvent inclure des variables dynamiques (numéro de commande, client, articles, etc.)</li>
                </ul>
            </div>
        </div>

        <div id="maintenance" class="tab-content hidden-tab">
            <h2>Actions de Maintenance</h2>

            <h3 class="section-title">🧹 Nettoyage des Données</h3>
            <p>Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('pdf_builder_clear_cache_maintenance', 'pdf_builder_clear_cache_nonce_maintenance'); ?>
                    <button type="submit" name="clear_cache" class="button button-secondary" style="width: 100%;">
                        🗑️ Vider le Cache
                    </button>
                </form>

                <button type="button" id="remove-temp-files-btn" class="button button-secondary" style="width: 100%;">
                    📁 Supprimer Fichiers Temp
                </button>

                <button type="button" id="optimize-db-btn" class="button button-secondary" style="width: 100%;">
                    ⚡ Optimiser BD
                </button>
            </div>

            <h3 class="section-title">🔧 Réparation & Réinitialisation</h3>
            <p>Réparez les templates corrompus et les paramètres invalides.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <button type="button" id="repair-templates-btn" class="button button-secondary" style="width: 100%;">
                    ✅ Réparer Templates
                </button>

                <button type="button" id="reset-settings-btn" class="button button-warning" style="width: 100%;">
                    ⚠️ Réinitialiser Paramètres
                </button>

                <button type="button" id="check-integrity-btn" class="button button-secondary" style="width: 100%;">
                    🔍 Vérifier Intégrité
                </button>
            </div>

            <h3 class="section-title">📊 Informations Système</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Version du Plugin</th>
                    <td>
                        <code><?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : '1.0.0'; ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Statut WordPress</th>
                    <td>
                        <span style="color: #46b450;">✓ WordPress <?php echo get_bloginfo('version'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mémoire Disponible</th>
                    <td>
                        <?php
                        $memory_limit = ini_get('memory_limit');
                        $color = (intval($memory_limit) >= 256) ? '#46b450' : '#ffb900';
                        ?>
                        <span style="color: <?php echo $color; ?>;"><?php echo esc_html($memory_limit); ?></span>
                        <p class="description">Minimum recommandé: 256MB</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Temps Max Exécution</th>
                    <td>
                        <?php
                        $max_exec = ini_get('max_execution_time');
                        $color = ($max_exec >= 300) ? '#46b450' : '#ffb900';
                        ?>
                        <span style="color: <?php echo $color; ?>;"><?php echo esc_html($max_exec); ?>s</span>
                        <p class="description">Minimum recommandé: 300s</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">WooCommerce</th>
                    <td>
                        <?php
                        if (class_exists('WooCommerce')) {
                            echo '<span style="color: #46b450;">✓ Installé</span>';
                        } else {
                            echo '<span style="color: #666;">○ Non détecté</span>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Nombre de Templates</th>
                    <td>
                        <?php
                        $template_count = count(get_posts([
                            'post_type' => 'pdf_template',
                            'posts_per_page' => -1
                        ]));
                        echo intval($template_count);
                        ?>
                    </td>
                </tr>
            </table>

            <!-- Avertissements de maintenance -->
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #856404;">⚠️ Avant la Maintenance</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li>✓ Faites toujours une <strong>sauvegarde</strong> avant les opérations de maintenance</li>
                    <li>✓ Testez en mode de débogage d'abord</li>
                    <li>✓ Vérifiez les logs après l'opération</li>
                    <li>✓ N'utilisez pas "Réinitialiser" sans raison importante</li>
                </ul>
            </div>

            <!-- Conseils performance -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #003d66;">💡 Conseils Performance</h3>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li>Videz régulièrement le cache (hebdomadaire en production)</li>
                    <li>Supprimez les fichiers temporaires tous les mois</li>
                    <li>Vérifiez l'intégrité du système mensuellement</li>
                    <li>Consultez les logs en cas de problème</li>
                    <li>Maintenez WordPress à jour</li>
                </ul>
            </div>
        </div>

        <div id="developpeur" class="tab-content hidden-tab">
            <h2>Paramètres Développeur</h2>
            <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

         <form method="post" id="developpeur-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_developpeur_nonce'); ?>
                <input type="hidden" name="submit_developpeur" value="1">

                <h3 class="section-title">🔐 Contrôle d'Accès</h3>

             <table class="form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="developer_enabled" value="1" <?php echo isset($settings['developer_enabled']) && $settings['developer_enabled'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode développeur</span>
                        </div>
                        <div class="toggle-description">Active le mode développeur avec logs détaillés</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
                    <td>
                        <!-- Champ username caché pour l'accessibilité -->
                        <input type="text" autocomplete="username" style="display: none;" />
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="password" id="developer_password" name="developer_password"
                                   placeholder="Laisser vide pour aucun mot de passe" autocomplete="current-password"
                                   style="width: 250px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   value="<?php echo esc_attr($settings['developer_password'] ?? ''); ?>" />
                            <button type="button" id="toggle_password" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                👁️ Afficher
                            </button>
                        </div>
                        <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
                        <?php if (!empty($settings['developer_password'])) :
                            ?>
                        <p class="description" style="color: #28a745;">✓ Mot de passe configuré et sauvegardé</p>
                            <?php
                        endif; ?>
                    </td>
                </tr>
             </table>

            <div id="dev-license-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">🔐 Test de Licence</h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="license_test_mode">Mode Test Licence</label></th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button type="button" id="toggle_license_test_mode_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                🎚️ Basculer Mode Test
                            </button>
                            <span id="license_test_mode_status" style="font-weight: bold; padding: 8px 12px; border-radius: 4px; <?php echo $license_test_mode ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                                <?php echo $license_test_mode ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif'; ?>
                            </span>
                        </div>
                        <p class="description">Basculer le mode test pour développer et tester sans serveur de licence en production</p>
                        <input type="checkbox" id="license_test_mode" name="license_test_mode" value="1" <?php echo $license_test_mode ? 'checked' : ''; ?> style="display: none;" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Clé de Test</label></th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" id="license_test_key" readonly style="width: 350px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;" placeholder="Générer une clé..." value="<?php echo esc_attr($license_test_key); ?>" />
                            <button type="button" id="generate_license_key_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                🔑 Générer
                            </button>
                            <button type="button" id="copy_license_key_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                📋 Copier
                            </button>
                            <?php if ($license_test_key) :
                                ?>
                            <button type="button" id="delete_license_key_btn" class="button button-link-delete" style="padding: 8px 12px; height: auto;">
                                🗑️ Supprimer
                            </button>
                                <?php
                            endif; ?>
                        </div>
                        <p class="description">Génère une clé de test aléatoire pour valider le système de licence</p>
                        <span id="license_key_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Nettoyage Complet</label></th>
                    <td>
                        <button type="button" id="cleanup_license_btn" class="button button-link-delete" style="padding: 10px 15px; height: auto; font-weight: bold;">
                            🧹 Nettoyer complètement la licence
                        </button>
                        <p class="description">Supprime tous les paramètres de licence et réinitialise à l'état libre. Utile pour les tests.</p>
                        <span id="cleanup_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                        <input type="hidden" id="cleanup_license_nonce" value="<?php echo wp_create_nonce('pdf_builder_cleanup_license'); ?>" />
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-debug-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">🔍 Paramètres de Debug</h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="debug_php_errors">Errors PHP</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_php_errors" name="debug_php_errors" value="1" <?php echo isset($settings['debug_php_errors']) && $settings['debug_php_errors'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug PHP</span>
                        </div>
                        <div class="toggle-description">Affiche les erreurs/warnings PHP du plugin</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_javascript">Debug JavaScript</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_javascript" name="debug_javascript" value="1" <?php echo isset($settings['debug_javascript']) && $settings['debug_javascript'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug JS</span>
                        </div>
                        <div class="toggle-description">Active les logs détaillés en console (emojis: 🚀 start, ✅ success, ❌ error, ⚠️ warn)</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_javascript_verbose">Logs Verbeux JS</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_javascript_verbose" name="debug_javascript_verbose" value="1" <?php echo isset($settings['debug_javascript_verbose']) && $settings['debug_javascript_verbose'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Logs détaillés</span>
                        </div>
                        <div class="toggle-description">Active les logs détaillés (rendu, interactions, etc.). À désactiver en production.</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_ajax">Debug AJAX</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_ajax" name="debug_ajax" value="1" <?php echo isset($settings['debug_ajax']) && $settings['debug_ajax'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug AJAX</span>
                        </div>
                        <div class="toggle-description">Enregistre toutes les requêtes AJAX avec requête/réponse</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_performance">Métriques Performance</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_performance" name="debug_performance" value="1" <?php echo isset($settings['debug_performance']) && $settings['debug_performance'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug perf.</span>
                        </div>
                        <div class="toggle-description">Affiche le temps d'exécution et l'utilisation mémoire des opérations</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_database">Requêtes BD</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_database" name="debug_database" value="1" <?php echo isset($settings['debug_database']) && $settings['debug_database'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug DB</span>
                        </div>
                        <div class="toggle-description">Enregistre les requêtes SQL exécutées par le plugin</div>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-logs-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Fichiers Logs</h3>

            <table class="form-table">
                <tr>
                  <th scope="row"><label for="log_level">Niveau de Log</label></th>
                    <td>
                        <select id="log_level" name="log_level" style="width: 200px;">
                            <option value="0" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 0) ? 'selected' : ''; ?>>Aucun log</option>
                            <option value="1" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 1) ? 'selected' : ''; ?>>Erreurs uniquement</option>
                            <option value="2" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 2) ? 'selected' : ''; ?>>Erreurs + Avertissements</option>
                            <option value="3" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 3) ? 'selected' : ''; ?>>Info complète</option>
                            <option value="4" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 4) ? 'selected' : ''; ?>>Détails (Développement)</option>
                        </select>
                        <p class="description">0=Aucun, 1=Erreurs, 2=Warn, 3=Info, 4=Détails</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_file_size">Taille Max Log</label></th>
                    <td>
                        <input type="number" id="log_file_size" name="log_file_size" value="<?php echo isset($settings['log_file_size']) ? intval($settings['log_file_size']) : '10'; ?>" min="1" max="100" /> MB
                        <p class="description">Rotation automatique quand le log dépasse cette taille</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_retention">Retention Logs</label></th>
                    <td>
                        <input type="number" id="log_retention" name="log_retention" value="<?php echo isset($settings['log_retention']) ? intval($settings['log_retention']) : '30'; ?>" min="1" max="365" /> jours
                        <p class="description">Supprime automatiquement les logs plus vieux que ce délai</p>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-optimizations-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Optimisations Avancées</h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="force_https">Forcer HTTPS API</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="force_https" name="force_https" value="1" <?php echo isset($settings['force_https']) && $settings['force_https'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">HTTPS forcé</span>
                        </div>
                        <div class="toggle-description">Force les appels API externes en HTTPS (sécurité renforcée)</div>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-logs-viewer-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Visualiseur de Logs Temps Réel</h3>

            <div style="margin-bottom: 15px;">
                <button type="button" id="refresh_logs_btn" class="button button-secondary">🔄 Actualiser Logs</button>
                <button type="button" id="clear_logs_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Logs</button>
                <select id="log_filter" style="margin-left: 10px;">
                    <option value="all">Tous les logs</option>
                    <option value="error">Erreurs uniquement</option>
                    <option value="warning">Avertissements</option>
                    <option value="info">Info</option>
                    <option value="debug">Debug</option>
                </select>
            </div>

            <div id="logs_container" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; line-height: 1.4;">
                <div id="logs_content" style="white-space: pre-wrap;">
                    <!-- Logs will be loaded here -->
                    <em style="color: #666;">Cliquez sur "Actualiser Logs" pour charger les logs récents...</em>
                </div>
            </div>
            </div>

            <div id="dev-tools-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Outils de Développement</h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <button type="button" id="reload_cache_btn" class="button button-secondary">
                    🔄 Recharger Cache
                </button>
                <button type="button" id="clear_temp_btn" class="button button-secondary">
                    🗑️ Vider Temp
                </button>
                <button type="button" id="test_routes_btn" class="button button-secondary">
                    🛣️ Tester Routes
                </button>
                <button type="button" id="export_diagnostic_btn" class="button button-secondary">
                    � Exporter Diagnostic
                </button>
                <button type="button" id="view_logs_btn" class="button button-secondary">
                    📋 Voir Logs
                </button>
                <button type="button" id="system_info_btn" class="button button-secondary">
                    ℹ️ Info Système
                </button>
            </div>
            </div>

            <div id="dev-shortcuts-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Raccourcis Clavier Développeur</h3>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 30%;">Raccourci</th>
                        <th style="width: 70%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd></td>
                        <td>Basculer le mode debug JavaScript</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>L</kbd></td>
                        <td>Ouvrir la console développeur du navigateur</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>R</kbd></td>
                        <td>Recharger la page (hard refresh)</td>
                    </tr>
                    <tr>
                        <td><kbd>F12</kbd></td>
                        <td>Ouvrir les outils développeur</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>U</kbd></td>
                        <td>Voir le code source de la page</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>I</kbd></td>
                        <td>Inspecter l'élément sous le curseur</td>
                    </tr>
                </tbody>
            </table>
            </div>

            <div id="dev-console-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Console Code</h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="test_code">Code Test</label></th>
                    <td>
                        <textarea id="test_code" style="width: 100%; height: 150px; font-family: monospace; padding: 10px;"></textarea>
                        <p class="description">Zone d'essai pour du code JavaScript (exécution côté client)</p>
                        <div style="margin-top: 10px;">
                            <button type="button" id="execute_code_btn" class="button button-secondary">▶️ Exécuter Code JS</button>
                            <button type="button" id="clear_console_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Console</button>
                            <span id="code_result" style="margin-left: 20px; font-weight: bold;"></span>
                        </div>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-hooks-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <!-- Tableau de références des hooks disponibles -->
            <h3 class="section-title">Hooks Disponibles</h3>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 25%;">Hook</th>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 25%;">Typage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>pdf_builder_before_generate</code></td>
                        <td>Avant la génération PDF</td>
                        <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_after_generate</code></td>
                        <td>Après la génération PDF réussie</td>
                        <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_template_data</code></td>
                        <td>Filtre les données de template</td>
                        <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_element_render</code></td>
                        <td>Rendu d'un élément du canvas</td>
                        <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_security_check</code></td>
                        <td>Vérifications de sécurité personnalisées</td>
                        <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_before_save</code></td>
                        <td>Avant sauvegarde des paramètres</td>
                        <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                    </tr>
                </tbody>
            </table>
            </div>

            <!-- Avertissement production -->
            <div style="background: #ffebee; border-left: 4px solid #d32f2f; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #c62828;">🚨 Avertissement Production</h3>
                <ul style="margin: 0; padding-left: 20px; color: #c62828;">
                    <li>❌ Ne jamais laisser le mode développeur ACTIVÉ en production</li>
                    <li>❌ Ne jamais afficher les logs détaillés aux utilisateurs</li>
                    <li>❌ Désactivez le profiling et les hooks de debug après débogage</li>
                    <li>❌ N'exécutez pas de code arbitraire en production</li>
                    <li>✓ Utilisez des mots de passe forts pour protéger les outils dev</li>
                </ul>
            </div>

            <!-- Conseils développement -->
            <div style="background: #f3e5f5; border-left: 4px solid #7b1fa2; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #4a148c;">💻 Conseils Développement</h3>
                <ul style="margin: 0; padding-left: 20px; color: #4a148c;">
                    <li>Activez Debug JavaScript pour déboguer les interactions client</li>
                    <li>Utilisez Debug AJAX pour vérifier les requêtes serveur</li>
                    <li>Consultez Debug Performance pour optimiser les opérations lentes</li>
                    <li>Lisez les logs détaillés (niveau 4) pour comprendre le flux</li>
                    <li>Testez avec les différents niveaux de log</li>
                </ul>
            </div>

            <p class="submit">
                <button type="submit" name="submit_developpeur" class="button button-primary">Enregistrer les paramètres développeur</button>
            </p>
         </form>
        </div>

        <!-- Bouton de sauvegarde flottant global -->
        <div class="floating-save-container">
            <button type="button" id="global-save-btn" class="floating-save-btn">
                💾 Enregistrer
            </button>
            <div class="save-status" id="save-status"></div>
        </div>

</div>
    <style>
            /* Configuration des notifications Toastr */
            .toast-top-right {
                position: fixed;
                top: 20px !important;
                right: 20px !important;
                z-index: 99999 !important;
            }

            .toast {
                animation: slideInRight 0.3s ease-out !important;
            }

            @keyframes slideInRight {
                from {
                    transform: translateX(420px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
    </style>

    <script>
        // Attendre que Toastr soit disponible puis attacher les événements
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction pour attendre Toastr
            function setupToastrNotifications() {
                if (typeof toastr === 'undefined') {
                    setTimeout(setupToastrNotifications, 100);
                    return;
                }
                // Configurer toastr
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                // Bouton Test du cache
                const testCacheBtn = document.getElementById('test-cache-btn');
                if (testCacheBtn) {
                    testCacheBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        toastr.info('🔍 Test du cache en cours...', 'Test');
                        setTimeout(() => {
                            toastr.success('✓ Cache fonctionne correctement !', 'Test Réussi');
                        }, 1500);
                    });
                }

                // Bouton Vider le cache
                const clearCacheBtn = document.getElementById('clear-cache-general-btn');
                if (clearCacheBtn) {
                    clearCacheBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        toastr.warning('🗑️ Vidage du cache en cours...', 'Vidage');
                        setTimeout(() => {
                            toastr.success('✓ Cache vidé avec succès !', 'Cache Vide');
                        }, 1500);
                    });
                }

                // Détecte la soumission du formulaire - EMPÊCHE LE RELOAD
                const settingsForm = document.getElementById('global-settings-form');
                if (settingsForm) {
                    settingsForm.addEventListener('submit', function(e) {
                        e.preventDefault(); // ✅ Empêche le rechargement de la page
                        // Afficher la notification de sauvegarde
                        toastr.info('💾 Enregistrement des paramètres en cours...', 'Sauvegarde');

                        // Récupérer les données du formulaire
                        const formData = new FormData(settingsForm);

                        // Envoyer en AJAX
                        fetch(settingsForm.action || window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            toastr.success('✅ Paramètres enregistrés avec succès !', 'Succès');
                        })
                        .catch(error => {
                            console.error('❌ Error submitting form:', error);
                            toastr.error('❌ Erreur lors de l\'enregistrement', 'Erreur');
                        });
                    });
                }

                // Bouton Enregistrer
                const submitBtn = document.getElementById('general-submit-btn');
                if (submitBtn) {
                    submitBtn.addEventListener('click', function(e) {
                        e.preventDefault(); // ✅ Empêche le rechargement
                        // Déclencher la soumission du formulaire
                        const settingsForm = document.getElementById('global-settings-form');
                        if (settingsForm) {
                            settingsForm.dispatchEvent(new Event('submit'));
                        }
                    });
                }
            }

            setupToastrNotifications();
        });
    </script>


    <style>
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
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
            background-color: #2196F3;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .toggle-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-label {
            font-weight: 500;
            color: #333;
        }

        .toggle-description {
            font-size: 12px;
            color: #666;
            margin: 0;
            padding-left: 60px;
        }

        .toggle-switch input:disabled ~ .toggle-slider {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .toggle-switch input:disabled ~ .toggle-label {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Inputs désactivés */
        input[type="number"]:disabled,
        input[type="text"]:disabled,
        input[type="email"]:disabled,
        select:disabled,
        textarea:disabled {
            background-color: #f5f5f5 !important;
            color: #999 !important;
            cursor: not-allowed !important;
            border-color: #ddd !important;
        }

        /* Bouton de sauvegarde flottant */
        .floating-save-container {
            position: fixed;
            bottom: 40px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .floating-save-btn {
            background: linear-gradient(135deg, #007cba 0%, #005a87 100%) !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 12px 24px !important;
            color: white !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            cursor: pointer !important;
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3) !important;
            transition: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            min-width: 140px !important;
            height: 44px !important;
            line-height: 1 !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            -webkit-appearance: none !important;
            appearance: none !important;
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
        }

        .floating-save-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #005a87 0%, #004466 100%) !important;
        }

        .floating-save-btn:disabled {
            background: linear-gradient(135deg, #cccccc 0%, #999999 100%) !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1) !important;
        }

        .floating-save-btn:active:not(:disabled) {
            background: linear-gradient(135deg, #004466 0%, #003344 100%) !important;
        }

        .floating-save-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .save-status {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            white-space: nowrap;
        }

        .save-status.show {
            opacity: 1;
        }

        .save-status.success {
            background: rgba(0, 128, 0, 0.9);
        }

        .save-status.error {
            background: rgba(220, 53, 69, 0.9);
        }

        /* Masquer les boutons individuels des onglets */
        .tab-content .submit {
            display: none;
        }

        /* Exception pour les onglets qui utilisent des formulaires POST séparés */
        #roles .submit,
        #notifications .submit {
            display: block;
        }

        /* Exception pour le bouton de test dans l'onglet notifications */
        #notifications #test-notifications,
        #notifications #test-smtp-connection {
            display: inline-block !important;
        }

        /* Cacher le bouton global flottant dans les onglets avec boutons individuels */
        #roles #global-save-btn {
            display: none !important;
        }

        /* Style pour les sections h3 */
        .section-title {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-left: 4px solid #007cba;
            border-radius: 8px;
            padding: 15px 20px !important;
            margin: 30px 0 20px 0 !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            color: #003d66 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-bottom: none !important;
        }

        /* Classe pour masquer les onglets non actifs */
        .hidden-tab {
            display: none;
        }
    </style>

    <?php
        // Définir les paramètres canvas pour JavaScript
        $canvas_settings_js = get_option('pdf_builder_canvas_settings', []);
    ?>
    <script>
        // Définir ajaxurl si pas déjà défini
        if (typeof ajaxurl === 'undefined') {
            ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        }
    </script>
    <script>
        // Script de définition des paramètres canvas - exécuté très tôt

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
            'show_margins' => $canvas_settings_js['show_margins'] ?? false,
            'show_grid' => $canvas_settings_js['show_grid'] ?? false,
            'grid_size' => $canvas_settings_js['grid_size'] ?? 10,
            'grid_color' => $canvas_settings_js['grid_color'] ?? '#e0e0e0',
            'snap_to_grid' => $canvas_settings_js['snap_to_grid'] ?? false,
            'snap_to_elements' => $canvas_settings_js['snap_to_elements'] ?? false,
            'snap_tolerance' => $canvas_settings_js['snap_tolerance'] ?? 5,
            'show_guides' => $canvas_settings_js['show_guides'] ?? false,
            'default_zoom' => $canvas_settings_js['default_zoom'] ?? 100,
            'zoom_step' => $canvas_settings_js['zoom_step'] ?? 25,
            'min_zoom' => $canvas_settings_js['min_zoom'] ?? 10,
            'max_zoom' => $canvas_settings_js['max_zoom'] ?? 500,
            'zoom_with_wheel' => $canvas_settings_js['zoom_with_wheel'] ?? false,
            'pan_with_mouse' => $canvas_settings_js['pan_with_mouse'] ?? false,
            'show_resize_handles' => $canvas_settings_js['show_resize_handles'] ?? false,
            'handle_size' => $canvas_settings_js['handle_size'] ?? 8,
            'handle_color' => $canvas_settings_js['handle_color'] ?? '#007cba',
            'enable_rotation' => $canvas_settings_js['enable_rotation'] ?? false,
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
    // NOTE: getDimensionsFromFormat function already defined above (line ~503), no need to duplicate it here
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du bouton de sauvegarde global
            function setupGlobalSaveButton() {
                const globalSaveBtn = document.getElementById('global-save-btn');
                const saveStatus = document.getElementById('save-status');
                if (globalSaveBtn) {
                    // Bloquer le mouvement du bouton
                    globalSaveBtn.addEventListener('mousedown', function(e) {
                        // Sauvegarder la position initiale
                        const rect = globalSaveBtn.getBoundingClientRect();
                        const startX = rect.left;
                        const startY = rect.top;
                        
                        // Forcer la position pendant le clic
                        globalSaveBtn.style.position = 'fixed !important';
                        globalSaveBtn.style.left = startX + 'px !important';
                        globalSaveBtn.style.top = startY + 'px !important';
                        
                        setTimeout(() => {
                            globalSaveBtn.style.position = '';
                            globalSaveBtn.style.left = '';
                            globalSaveBtn.style.top = '';
                        }, 100);
                    });

                    // ===== INITIALISER LE BOUTON COMME DÉSACTIVÉ =====
                    globalSaveBtn.disabled = true;
                    let hasUnsavedChanges = false;

                    // ===== TRACKER LES MODIFICATIONS DES FORMULAIRES (AMÉLIORÉ) =====
                    const setupFormTracking = () => {
                        const forms = document.querySelectorAll('form[id], form');
                        forms.forEach((form, formIndex) => {
                            // Récupérer les valeurs initiales de tous les inputs
                            const initialState = {};
                            const formInputs = form.querySelectorAll('input, select, textarea');
                            
                            formInputs.forEach(input => {
                                if (input.type === 'checkbox' || input.type === 'radio') {
                                    initialState[input.name] = input.checked;
                                } else {
                                    initialState[input.name] = input.value;
                                }
                            });
                            // Debounce pour éviter trop d'appels
                            let debounceTimer = null;
                            const markAsModified = () => {
                                clearTimeout(debounceTimer);
                                debounceTimer = setTimeout(() => {
                                    hasUnsavedChanges = true;
                                    globalSaveBtn.disabled = false;
                                    
                                    // Ajouter un badge visuel au bouton
                                    if (!globalSaveBtn.dataset.hasModifications) {
                                        globalSaveBtn.dataset.hasModifications = 'true';
                                        globalSaveBtn.setAttribute('title', '✏️ Modifications non sauvegardées');
                                    }
                                }, 300);
                            };
                            
                            // Ajouter des listeners change à tous les inputs
                            formInputs.forEach(input => {
                                input.addEventListener('change', function() {
                                    // Vérifier si une valeur a réellement changé
                                    const currentValue = (this.type === 'checkbox' || this.type === 'radio') ? this.checked : this.value;
                                    const hasChanged = initialState[this.name] !== currentValue;
                                    
                                    if (hasChanged) {
                                        markAsModified();
                                    }
                                });
                                
                                // Ajouter listeners input pour les champs texte (en temps réel avec debounce)
                                if (input.type === 'text' || input.type === 'email' || input.type === 'number' || input.tagName === 'TEXTAREA') {
                                    input.addEventListener('input', function() {
                                        const currentValue = this.value;
                                        const hasChanged = initialState[this.name] !== currentValue;
                                        if (hasChanged) {
                                            markAsModified();
                                        }
                                    });
                                }
                            });
                        });
                    };
                    
                    // Appliquer le tracking
                    setupFormTracking();
                    
                    // ===== GESTION DU CHAMP AUTO-SAVE INTERVAL =====
                    const autoSaveEnabledCheckbox = document.getElementById('auto_save_enabled');
                    const autoSaveIntervalInput = document.getElementById('auto_save_interval');
                    
                    if (autoSaveEnabledCheckbox && autoSaveIntervalInput) {
                        // Fonction pour mettre à jour l'état du champ interval
                        const updateAutoSaveIntervalState = () => {
                            if (autoSaveEnabledCheckbox.checked) {
                                autoSaveIntervalInput.disabled = false;
                                autoSaveIntervalInput.style.opacity = '1';
                                autoSaveIntervalInput.style.cursor = 'auto';
                            } else {
                                autoSaveIntervalInput.disabled = true;
                                autoSaveIntervalInput.style.opacity = '0.6';
                                autoSaveIntervalInput.style.cursor = 'not-allowed';
                            }
                        };
                        
                        // Appliquer l'état initial
                        updateAutoSaveIntervalState();
                        
                        // Ajouter un listener au checkbox pour mettre à jour l'état en temps réel
                        autoSaveEnabledCheckbox.addEventListener('change', updateAutoSaveIntervalState);
                    }
                    
                    // ===== GESTION DU CHAMP FPS MAXIMUM =====
                    const limitFpsCheckbox = document.getElementById('limit_fps');
                    const maxFpsInput = document.getElementById('max_fps');
                    
                    if (limitFpsCheckbox && maxFpsInput) {
                        // Fonction pour mettre à jour l'état du champ max_fps
                        const updateMaxFpsState = () => {
                            if (limitFpsCheckbox.checked) {
                                maxFpsInput.disabled = false;
                                maxFpsInput.style.opacity = '1';
                                maxFpsInput.style.cursor = 'auto';
                            } else {
                                maxFpsInput.disabled = true;
                                maxFpsInput.style.opacity = '0.6';
                                maxFpsInput.style.cursor = 'not-allowed';
                            }
                        };
                        
                        // Appliquer l'état initial
                        updateMaxFpsState();
                        
                        // Ajouter un listener au checkbox pour mettre à jour l'état en temps réel
                        limitFpsCheckbox.addEventListener('change', updateMaxFpsState);
                    }
                    
                    // ===== AVERTISSEMENT AVANT DE QUITTER =====
                    window.addEventListener('beforeunload', function(e) {
                        if (hasUnsavedChanges && !globalSaveBtn.disabled) {
                            e.preventDefault();
                            e.returnValue = '⚠️ Vous avez des modifications non sauvegardées. Êtes-vous sûr de vouloir quitter ?';
                            return e.returnValue;
                        }
                    });

                    globalSaveBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Trouver l'onglet actif (celui qui n'a pas la classe hidden-tab)
                        const activeTab = document.querySelector('.tab-content:not(.hidden-tab)') ||
                                        document.querySelector('.tab-content.active');

                        if (activeTab) {
                            // Trouver le formulaire dans l'onglet actif
                            let form = activeTab.querySelector('form');

                            // Si pas de formulaire direct, utiliser le formulaire global (fallback)
                            if (!form) {
                                form = document.getElementById('global-settings-form');
                            }

                            if (form) {
                                // Afficher notification via Toastr
                                if (typeof toastr !== 'undefined') {
                                    toastr.info('💾 Sauvegarde en cours...', 'Sauvegarde');
                                }

                                // Créer FormData à partir du formulaire
                                const formData = new FormData(form);
                                
                                // S'assurer que toutes les checkboxes sont incluses (même non cochées)
                                const allCheckboxes = form.querySelectorAll('input[type="checkbox"]');
                                allCheckboxes.forEach(checkbox => {
                                    if (!formData.has(checkbox.name)) {
                                        // Checkbox non cochée, l'ajouter avec valeur '0'
                                        formData.append(checkbox.name, '0');
                                    }
                                });
                                
                                // Utiliser toujours le gestionnaire générique pdf_builder_save_settings_page
                                // qui accepte tous les paramètres indépendamment de l'onglet
                                formData.append('action', 'pdf_builder_save_settings_page');
                                
                                // Récupérer le nonce du formulaire
                                // Essayer d'abord le nonce spécifique, puis fallback au nonce générique
                                let nonceName = 'pdf_builder_settings_nonce';
                                
                                if (activeTab.id === 'performance' || activeTab.id === 'maintenance') {
                                    nonceName = 'pdf_builder_performance_nonce';
                                } else if (activeTab.id === 'pdf') {
                                    nonceName = 'pdf_builder_pdf_nonce';
                                } else if (activeTab.id === 'developpeur') {
                                    nonceName = 'pdf_builder_developpeur_nonce';
                                }
                                
                                const nonceField = form.querySelector(`input[name="${nonceName}"]`);
                                if (nonceField) {
                                    // Renommer le champ du nonce à 'nonce' pour le gestionnaire AJAX
                                    formData.delete(nonceName);
                                    formData.append('nonce', nonceField.value);
                                } else {
                                    console.warn('⚠️ Nonce field non trouvé:', nonceName);
                                }

                                // Log des données qui vont être envoyées (pour debug)
                                for (let [key, value] of formData.entries()) {
                                }

                                // Faire la requête AJAX
                                fetch(ajaxurl, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => {
                                    // Toujours récupérer le texte pour pouvoir l'afficher en cas d'erreur
                                    return response.text().then(text => ({
                                        status: response.status,
                                        ok: response.ok,
                                        contentType: response.headers.get('content-type'),
                                        body: text
                                    }));
                                })
                                .then(({status, ok, contentType, body}) => {
                                    // Vérifier si c'est du JSON valide
                                    if (!contentType || !contentType.includes('application/json')) {
                                        throw new Error(`Réponse non-JSON du serveur (Status: ${status}). Contenu: ${body.substring(0, 500)}`);
                                    }
                                    
                                    if (!ok) {
                                        throw new Error(`Erreur HTTP ${status}: ${body.substring(0, 500)}`);
                                    }
                                    
                                    // Parser le JSON
                                    try {
                                        return JSON.parse(body);
                                    } catch (e) {
                                        throw new Error(`Erreur JSON invalid: ${e.message}. Contenu: ${body.substring(0, 500)}`);
                                    }
                                })
                                .then(data => {
                                    if (data.success) {
                                        if (typeof toastr !== 'undefined') {
                                            toastr.success('✅ Paramètres sauvegardés avec succès !', 'Succès');
                                        }
                                        
                                        // ===== RÉINITIALISER L'ÉTAT APRÈS SAUVEGARDE =====
                                        hasUnsavedChanges = false;
                                        
                                        // Réactualiser l'état initial de tous les inputs pour tracker les futures modifications
                                        setupFormTracking();
                                        
                                        // Réinitialiser le bouton Enregistrer comme désactivé
                                        globalSaveBtn.disabled = true;
                                        globalSaveBtn.dataset.hasModifications = 'false';
                                        globalSaveBtn.removeAttribute('title');
                                    } else {
                                        if (typeof toastr !== 'undefined') {
                                            toastr.error('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'Erreur');
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('❌ AJAX Error:', error);
                                    if (typeof toastr !== 'undefined') {
                                        toastr.error('❌ ' + error.message, 'Erreur');
                                    }
                                });
                            } else {
                                console.error('❌ No form found in active tab:', activeTab.id);
                                if (typeof toastr !== 'undefined') {
                                    toastr.error('❌ Aucun formulaire trouvé', 'Erreur');
                                }
                            }
                        } else {
                            console.error('❌ No active tab found');
                            if (typeof toastr !== 'undefined') {
                                toastr.error('❌ Aucun onglet actif', 'Erreur');
                            }
                        }
                    });
                }

                // Gestion du bouton Vider le Cache
                const clearCacheBtn = document.getElementById('clear-cache-btn');
                if (clearCacheBtn) {
                    clearCacheBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        if (confirm('Êtes-vous sûr de vouloir vider le cache ? Cette action est irréversible.')) {
                            // Afficher le statut
                            if (saveStatus) {
                                saveStatus.textContent = '🗑️ Vidage du cache...';
                                saveStatus.style.color = '#007cba';
                            }

                            // Faire une requête AJAX pour vider le cache
                            const formData = new FormData();
                            formData.append('action', 'pdf_builder_clear_cache');
                            formData.append('security', '<?php echo esc_js(wp_create_nonce("pdf_builder_clear_cache_performance")); ?>');

                            fetch(ajaxurl, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    if (saveStatus) {
                                        saveStatus.textContent = '✅ Cache vidé avec succès';
                                        saveStatus.style.color = '#46b450';
                                    }
                                    setTimeout(() => {
                                        if (saveStatus) saveStatus.classList.add('show');
                                    }, 100);
                                    setTimeout(() => {
                                        if (saveStatus) {
                                            saveStatus.classList.remove('show');
                                            saveStatus.textContent = '';
                                        }
                                    }, 3000);
                                } else {
                                    if (saveStatus) {
                                        saveStatus.textContent = '❌ Erreur lors du vidage du cache';
                                        saveStatus.style.color = '#dc3232';
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Erreur AJAX:', error);
                                if (saveStatus) {
                                    saveStatus.textContent = '❌ Erreur de connexion';
                                    saveStatus.style.color = '#dc3232';
                                }
                            });
                        }
                    });
                }

                // Fonction helper pour les requêtes AJAX de maintenance (non-bloquante)
                const sendMaintenanceAjax = (action, nonce) => {
                    const formData = new FormData();
                    formData.append('action', action);
                    formData.append('nonce', nonce);
                    
                    fetch(ajaxurl, { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(data => {
                            if (typeof toastr !== 'undefined') {
                                if (data.success) {
                                    toastr.success('✅ ' + data.message, 'Succès');
                                    if (action === 'pdf_builder_reset_settings') {
                                        setTimeout(() => location.reload(), 2000);
                                    }
                                } else {
                                    toastr.error('❌ ' + data.message, 'Erreur');
                                }
                            }
                        })
                        .catch(error => {
                            if (typeof toastr !== 'undefined') {
                                toastr.error('❌ Erreur: ' + error.message, 'Erreur');
                            }
                        });
                };

                // ===== BOUTON SUPPRIMER FICHIERS TEMP =====
                const removeTempFilesBtn = document.getElementById('remove-temp-files-btn');
                if (removeTempFilesBtn) {
                    removeTempFilesBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof toastr !== 'undefined') toastr.info('📁 Suppression...', 'En cours');
                        sendMaintenanceAjax('pdf_builder_remove_temp_files', '<?php echo esc_js(wp_create_nonce("pdf_builder_remove_temp")); ?>');
                    });
                }                // ===== BOUTON OPTIMISER BD =====
                const optimizeDbBtn = document.getElementById('optimize-db-btn');
                if (optimizeDbBtn) {
                    optimizeDbBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof toastr !== 'undefined') toastr.info('⚡ Optimisation...', 'En cours');
                        sendMaintenanceAjax('pdf_builder_optimize_db', '<?php echo esc_js(wp_create_nonce("pdf_builder_optimize_db")); ?>');
                    });
                }

                // ===== BOUTON RÉPARER TEMPLATES =====
                const repairTemplatesBtn = document.getElementById('repair-templates-btn');
                if (repairTemplatesBtn) {
                    repairTemplatesBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof toastr !== 'undefined') toastr.info('✅ Réparation...', 'En cours');
                        sendMaintenanceAjax('pdf_builder_repair_templates', '<?php echo esc_js(wp_create_nonce("pdf_builder_repair_templates")); ?>');
                    });
                }

                // ===== BOUTON RÉINITIALISER PARAMÈTRES =====
                const resetSettingsBtn = document.getElementById('reset-settings-btn');
                if (resetSettingsBtn) {
                    resetSettingsBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (!confirm('⚠️ ATTENTION: Réinitialiser tous les paramètres ? Cette action est IRRÉVERSIBLE !')) return;
                        if (typeof toastr !== 'undefined') toastr.warning('⚠️ Réinitialisation...', 'En cours');
                        sendMaintenanceAjax('pdf_builder_reset_settings', '<?php echo esc_js(wp_create_nonce("pdf_builder_reset_settings")); ?>');
                    });
                }

                // ===== BOUTON VÉRIFIER INTÉGRITÉ =====
                const checkIntegrityBtn = document.getElementById('check-integrity-btn');
                if (checkIntegrityBtn) {
                    checkIntegrityBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof toastr !== 'undefined') toastr.info('🔍 Vérification...', 'En cours');
                        sendMaintenanceAjax('pdf_builder_check_integrity', '<?php echo esc_js(wp_create_nonce("pdf_builder_check_integrity")); ?>');
                    });
                }
            }

            // Démarrer la gestion du bouton global
            setupGlobalSaveButton();
        });
    </script>

    <script>
            // Gestion de la navigation des onglets
            function setupTabNavigation() {
                // Initialiser la visibilité du bouton global selon l'onglet actif au chargement
                const initialActiveTab = document.querySelector('.tab-content:not(.hidden-tab)');
                const globalSaveBtn = document.getElementById('global-save-btn');
                if (globalSaveBtn && initialActiveTab) {
                    if (initialActiveTab.id === 'maintenance') {
                        globalSaveBtn.style.display = 'none';
                    } else {
                        globalSaveBtn.style.display = '';
                    }
                }

                const tabLinks = document.querySelectorAll('.nav-tab[data-tab]');
                tabLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();

                        const targetTab = this.getAttribute('data-tab');

                        // Masquer tous les onglets
                        const allTabs = document.querySelectorAll('.tab-content');
                        allTabs.forEach(tab => {
                            tab.classList.add('hidden-tab');
                        });

                        // Désactiver tous les liens d'onglets
                        document.querySelectorAll('.nav-tab').forEach(tabLink => {
                            tabLink.classList.remove('nav-tab-active');
                        });

                        // Afficher l'onglet cible
                        const targetTabContent = document.getElementById(targetTab);
                        if (targetTabContent) {
                            targetTabContent.classList.remove('hidden-tab');
                        } else {
                            console.error('❌ TAB NOT FOUND:', targetTab);
                        }

                        // Activer le lien d'onglet
                        this.classList.add('nav-tab-active');

                        // Gérer la visibilité du bouton de sauvegarde global
                        const globalSaveBtn = document.getElementById('global-save-btn');
                        if (globalSaveBtn) {
                            if (targetTab === 'maintenance') {
                                globalSaveBtn.style.display = 'none';
                            } else {
                                globalSaveBtn.style.display = '';
                            }
                        }

                        // Sauvegarder l'onglet actif dans localStorage
                        localStorage.setItem('pdf_builder_active_tab', targetTab);
                    });
                });

                // Restaurer l'onglet actif depuis localStorage
                const savedTab = localStorage.getItem('pdf_builder_active_tab');
                if (savedTab) {
                    const savedTabLink = document.querySelector(`.nav-tab[data-tab="${savedTab}"]`);
                    if (savedTabLink) {
                        savedTabLink.click();
                    }
                }
            }

            // Démarrer la navigation des onglets
            setupTabNavigation();

            // Gestion du bouton toggle password
            const togglePasswordBtn = document.getElementById('toggle_password');
            const passwordInput = document.getElementById('developer_password');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        this.innerHTML = '🙈 Masquer';
                    } else {
                        passwordInput.type = 'password';
                        this.innerHTML = '👁️ Afficher';
                    }
                });
            }

            // Gestion du générateur de clé de licence
            const generateLicenseKeyBtn = document.getElementById('generate_license_key_btn');
            const copyLicenseKeyBtn = document.getElementById('copy_license_key_btn');
            const licenseTestKeyInput = document.getElementById('license_test_key');
            const licenseKeyStatus = document.getElementById('license_key_status');

            if (generateLicenseKeyBtn) {
                generateLicenseKeyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const $btn = jQuery(this);
                    $btn.prop('disabled', true);
                    $btn.html('⏳ Génération...');

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'pdf_builder_generate_test_license_key',
                            nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_generate_license_key')); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.key) {
                                licenseTestKeyInput.value = response.data.key;
                                licenseKeyStatus.innerHTML = '<span style="color: #28a745;">✅ Clé générée avec succès!</span>';
                                $btn.html('🔑 Régénérer');
                                $btn.prop('disabled', false);
                            } else {
                                const errorMsg = response.data && response.data.message ? response.data.message : 'Impossible de générer la clé';
                                licenseKeyStatus.innerHTML = '<span style="color: #d32f2f; background: #f8d7da; padding: 8px 12px; border-radius: 4px; display: inline-block;">⚠️ Erreur: ' + errorMsg + '</span>';
                                $btn.html('🔑 Générer');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ AJAX error:', error);
                            let errorMsg = error;
                            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                errorMsg = xhr.responseJSON.data.message;
                            }
                            licenseKeyStatus.innerHTML = '<span style="color: #d32f2f; background: #f8d7da; padding: 8px 12px; border-radius: 4px; display: inline-block;">⚠️ Erreur AJAX: ' + errorMsg + '</span>';
                            $btn.html('🔑 Générer');
                            $btn.prop('disabled', false);
                        }
                    });
                });
            }

            if (copyLicenseKeyBtn) {
                copyLicenseKeyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (licenseTestKeyInput.value) {
                        navigator.clipboard.writeText(licenseTestKeyInput.value).then(function() {
                            licenseKeyStatus.innerHTML = '<span style="color: #007cba;">📋 Clé copiée !</span>';
                            setTimeout(function() {
                                licenseKeyStatus.innerHTML = '';
                            }, 3000);
                        }).catch(function(err) {
                            console.error('❌ Copy failed:', err);
                            licenseKeyStatus.innerHTML = '<span style="color: #d32f2f;">❌ Impossible de copier</span>';
                        });
                    } else {
                        licenseKeyStatus.innerHTML = '<span style="color: #d32f2f;">❌ Aucune clé à copier</span>';
                    }
                });
            }

            // Gestion de la suppression de la clé de test
            const deleteLicenseKeyBtn = document.getElementById('delete_license_key_btn');

            if (deleteLicenseKeyBtn) {
                deleteLicenseKeyBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer la clé de test ? Cette action est irréversible.')) {
                        return;
                    }
                    const $btn = jQuery(this);
                    $btn.prop('disabled', true);
                    $btn.html('⏳ Suppression...');

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'pdf_builder_delete_test_license_key',
                            nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_delete_test_license_key')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                licenseTestKeyInput.value = '';
                                licenseKeyStatus.innerHTML = '<span style="color: #155724; background: #d4edda; padding: 8px 12px; border-radius: 4px; display: inline-block;">✅ Clé supprimée avec succès !</span>';

                                // Masquer le bouton de suppression
                                $btn.hide();

                                setTimeout(function() {
                                    licenseKeyStatus.innerHTML = '';
                                }, 3000);
                            } else {
                                const errorMsg = response.data && response.data.message ? response.data.message : 'Impossible de supprimer la clé';
                                console.error('❌ Delete failed:', errorMsg);
                                licenseKeyStatus.innerHTML = '<span style="color: #d32f2f; background: #f8d7da; padding: 8px 12px; border-radius: 4px; display: inline-block;">⚠️ Erreur: ' + errorMsg + '</span>';
                                $btn.html('🗑️ Supprimer');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ AJAX error:', error);
                            let errorMsg = error;
                            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                errorMsg = xhr.responseJSON.data.message;
                            }
                            licenseKeyStatus.innerHTML = '<span style="color: #d32f2f; background: #f8d7da; padding: 8px 12px; border-radius: 4px; display: inline-block;">⚠️ Erreur AJAX: ' + errorMsg + '</span>';
                            $btn.html('🗑️ Supprimer');
                            $btn.prop('disabled', false);
                        }
                    });
                });
            }

            // Gestion du basculement du mode test de licence
            const toggleTestModeBtn = document.getElementById('toggle_license_test_mode_btn');
            const testModeStatus = document.getElementById('license_test_mode_status');
            const testModeCheckbox = document.getElementById('license_test_mode');

            if (toggleTestModeBtn) {
                toggleTestModeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const $btn = jQuery(this);
                    $btn.prop('disabled', true);
                    $btn.html('⏳ Basculement...');

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'pdf_builder_toggle_test_mode',
                            nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_toggle_test_mode')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                const enabled = response.data.enabled;

                                // Mettre à jour le statut
                                if (enabled) {
                                    testModeStatus.innerHTML = '✅ MODE TEST ACTIF';
                                    testModeStatus.style.background = '#d4edda';
                                    testModeStatus.style.color = '#155724';
                                } else {
                                    testModeStatus.innerHTML = '❌ Mode test inactif';
                                    testModeStatus.style.background = '#f8d7da';
                                    testModeStatus.style.color = '#721c24';
                                }

                                // Mettre à jour le checkbox caché
                                if (testModeCheckbox) {
                                    testModeCheckbox.checked = enabled;
                                }

                                $btn.html('🎚️ Basculer Mode Test');
                                $btn.prop('disabled', false);
                            } else {
                                const errorMsg = response.data && response.data.message ? response.data.message : 'Erreur lors du basculement';
                                console.error('❌ Toggle failed:', errorMsg);
                                alert('⚠️ Erreur: ' + errorMsg);
                                $btn.html('🎚️ Basculer Mode Test');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ AJAX error:', error);
                            let errorMsg = error;
                            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                errorMsg = xhr.responseJSON.data.message;
                            }
                            alert('⚠️ Erreur AJAX: ' + errorMsg);
                            $btn.html('🎚️ Basculer Mode Test');
                            $btn.prop('disabled', false);
                        }
                    });
                });
            }

            // Gestion du nettoyage complet de la licence
            const cleanupBtn = document.getElementById('cleanup_license_btn');
            if (cleanupBtn) {
                cleanupBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Confirmation avant de nettoyer
                    if (!confirm('⚠️ Êtes-vous sûr ? Cela supprimera TOUS les paramètres de licence.\nLa licence sera réinitialisée à l\'état libre.')) {
                        return;
                    }
                    const $btn = jQuery(this);
                    const cleanupStatus = document.getElementById('cleanup_status');
                    const cleanupNonce = document.getElementById('cleanup_license_nonce');
                    $btn.prop('disabled', true);
                    $btn.html('⏳ Nettoyage...');
                    cleanupStatus.innerHTML = '';

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'pdf_builder_cleanup_license',
                            nonce: cleanupNonce ? cleanupNonce.value : ''
                        },
                        success: function(response) {
                            $btn.html('🧹 Nettoyer complètement la licence');
                            $btn.prop('disabled', false);

                            if (response.success) {
                                cleanupStatus.innerHTML = '<span style="color: #155724; background: #d4edda; padding: 8px 12px; border-radius: 4px; display: inline-block;">✅ ' + response.data.message + '</span>';

                                // Recharger la page après 2 secondes pour voir les changements
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                const errorMsg = response.data && response.data.message ? response.data.message : 'Erreur lors du nettoyage';
                                console.error('❌ Cleanup failed:', errorMsg);
                                cleanupStatus.innerHTML = '<span style="color: #d32f2f; background: #f8d7da; padding: 8px 12px; border-radius: 4px; display: inline-block;">⚠️ Erreur: ' + errorMsg + '</span>';
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ AJAX error:', error);
                            let errorMsg = error;
                            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                errorMsg = xhr.responseJSON.data.message;
                            }
                            alert('⚠️ Erreur AJAX: ' + errorMsg);
                            cleanupStatus.innerHTML = '<span style="color: #d32f2f; background: #f8d7da; padding: 8px 12px; border-radius: 4px; display: inline-block;">⚠️ Erreur AJAX: ' + errorMsg + '</span>';
                            $btn.html('🧹 Nettoyer complètement la licence');
                            $btn.prop('disabled', false);
                        }
                    });
                });
            }

                    // Gestion du test du système de cache
            jQuery(document).ready(function($) {
                const $btn = $("#test-cache-btn");
                const $results = $("#cache-test-results");
                const $output = $("#cache-test-output");

                $btn.on("click", function(e) {
                    e.preventDefault();
                    $btn.prop("disabled", true).html("🔄 Test en cours...");
                    if ($results.length) $results.html('<span style="color: #007cba;">Test en cours...</span>');
                    if ($output.length) $output.hide();

                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        dataType: "json",
                        data: {
                            action: "pdf_builder_simple_test"
                        },
                        timeout: 30000,
                        success: function(response) {
                            $btn.prop("disabled", false).html("🧪 Tester l'intégration du cache");

                            if (response.success) {
                                if ($results.length) $results.html('<span style="color: #28a745;">✓ Test réussi</span>');
                                if ($output.length) $output.html(response.data).show();
                            } else {
                                if ($results.length) $results.html('<span style="color: #dc3545;">✗ Test échoué</span>');
                                if ($output.length) $output.html('<p>Erreur: ' + (response.data || 'Réponse invalide') + '</p>').show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("❌ AJAX error:", status, error);
                            $btn.prop("disabled", false).html("🧪 Tester l'intégration du cache");

                            if ($results.length) $results.html('<span style="color: #dc3545;">✗ Erreur HTTP ' + xhr.status + '</span>');
                            if ($output.length) $output.html('<p>Erreur: ' + error + '</p>').show();
                        }
                    });
                });
            });

            // ===== GESTION DES BOUTONS DE TEST SMTP ET NOTIFICATIONS =====
            jQuery(document).ready(function($) {
                // Test SMTP Connection
                const $testSmtpBtn = $("#test-smtp-connection");
                if ($testSmtpBtn.length) {
                    $testSmtpBtn.on("click", function(e) {
                        e.preventDefault();
                        const originalText = $testSmtpBtn.html();
                        $testSmtpBtn.prop("disabled", true).html("🔄 Test en cours...");

                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: "pdf_builder_test_smtp_connection",
                                nonce: "<?php echo esc_js(wp_create_nonce('pdf_builder_settings')); ?>"
                            },
                            timeout: 15000,
                            success: function(response) {
                                $testSmtpBtn.prop("disabled", false).html(originalText);

                                if (response.success) {
                                    alert("✅ Connexion SMTP réussie!\n\n" + (response.data.message || "La connexion au serveur SMTP fonctionne correctement."));
                                } else {
                                    alert("❌ Erreur de connexion SMTP\n\n" + (response.data.message || "Impossible de se connecter au serveur SMTP."));
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("❌ SMTP Test AJAX error:", status, error);
                                $testSmtpBtn.prop("disabled", false).html(originalText);
                                alert("⚠️ Erreur lors du test SMTP\n\nErreur: " + error);
                            }
                        });
                    });
                }

                // Test Notifications
                const $testNotifBtn = $("#test-notifications");
                if ($testNotifBtn.length) {
                    $testNotifBtn.on("click", function(e) {
                        e.preventDefault();
                        const originalText = $testNotifBtn.html();
                        $testNotifBtn.prop("disabled", true).html("🔄 Envoi en cours...");

                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: "pdf_builder_test_notifications",
                                nonce: "<?php echo esc_js(wp_create_nonce('pdf_builder_settings')); ?>"
                            },
                            timeout: 15000,
                            success: function(response) {
                                $testNotifBtn.prop("disabled", false).html(originalText);

                                if (response.success) {
                                    alert("✅ Email de test envoyé!\n\n" + (response.data.message || "Vérifiez votre boîte mail pour confirmer la réception."));
                                } else {
                                    alert("❌ Erreur lors de l'envoi\n\n" + (response.data.message || "Impossible d'envoyer l'email de test."));
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("❌ Notification Test AJAX error:", status, error);
                                $testNotifBtn.prop("disabled", false).html(originalText);
                                alert("⚠️ Erreur lors du test de notification\n\nErreur: " + error);
                            }
                        });
                    });
                }
            });

            // Gestion dynamique des champs grille
            jQuery('#show_grid').on('change', function() {
                var isChecked = jQuery(this).is(':checked');
                var $gridSizeInput = jQuery('#grid_size');
                var $gridColorInput = jQuery('#grid_color');
                var $gridSizeLabel = jQuery('label[for="grid_size"]');
                var $gridColorLabel = jQuery('label[for="grid_color"]');

                if (isChecked) {
                    $gridSizeInput.prop('disabled', false).css({'background-color': '', 'color': ''});
                    $gridColorInput.prop('disabled', false).css('opacity', '');
                    $gridSizeLabel.css('color', '');
                    $gridColorLabel.css('color', '');
                } else {
                    $gridSizeInput.prop('disabled', true).css({'background-color': '#f0f0f0', 'color': '#999'});
                    $gridColorInput.prop('disabled', true).css('opacity', '0.6');
                    $gridSizeLabel.css('color', '#999');
                    $gridColorLabel.css('color', '#999');
                }
            });

            // Gestion dynamique des champs cache
            jQuery('#cache_enabled').on('change', function() {
                var isChecked = jQuery(this).is(':checked');
                var $cacheTtlInput = jQuery('#cache_ttl');
                var $cacheTtlLabel = jQuery('label[for="cache_ttl"]');

                if (isChecked) {
                    $cacheTtlInput.prop('disabled', false).css({'background-color': '', 'color': ''});
                    $cacheTtlLabel.css('color', '');
                } else {
                    $cacheTtlInput.prop('disabled', true).css({'background-color': '#f0f0f0', 'color': '#999'});
                    $cacheTtlLabel.css('color', '#999');
                }
            });

            // Initialiser l'état des champs cache au chargement
            jQuery(document).ready(function() {
                var cacheEnabled = jQuery('#cache_enabled').is(':checked');
                var $cacheTtlInput = jQuery('#cache_ttl');
                var $cacheTtlLabel = jQuery('label[for="cache_ttl"]');

                if (!cacheEnabled) {
                    $cacheTtlInput.prop('disabled', true).css({'background-color': '#f0f0f0', 'color': '#999'});
                    $cacheTtlLabel.css('color', '#999');
                }
            });

            // Fonction pour mettre à jour l'état des marges
            function updateMarginsState() {
                var isChecked = jQuery('#show_margins').is(':checked');
                var $marginInputs = jQuery('.margin-input');
                var $marginLabels = jQuery('.margin-label');

                if (isChecked) {
                    $marginInputs.prop('disabled', false).css({'background-color': '', 'color': ''});
                    $marginLabels.css('color', '');
                } else {
                    $marginInputs.prop('disabled', true).css({'background-color': '#f0f0f0', 'color': '#999'});
                    $marginLabels.css('color', '#999');
                }
            }

            // Gestion dynamique des champs marges - event listener
            jQuery('#show_margins').on('change', function() {
                updateMarginsState();
            });

            // Initialiser l'état des champs marges au chargement
            jQuery(document).ready(function() {
                setTimeout(updateMarginsState, 100);
            });

            // Également initialiser après un délai pour être sûr que les éléments sont chargés
            window.addEventListener('load', function() {
                updateMarginsState();
            });

            // Synchronisation automatique des paramètres PDF avec les paramètres Canvas
            jQuery('#default_canvas_format, #default_canvas_orientation').on('change', function() {
                var canvasFormat = jQuery('#default_canvas_format').val();
                var canvasOrientation = jQuery('#default_canvas_orientation').val();

                // Synchroniser le format PDF avec le format Canvas (seulement si c'est un format standard)
                var standardFormats = ['A4', 'A3', 'Letter', 'Legal'];
                if (standardFormats.includes(canvasFormat)) {
                    jQuery('#default_format').val(canvasFormat);
                }

                // Synchroniser l'orientation PDF avec l'orientation Canvas
                jQuery('#default_orientation').val(canvasOrientation);
            });

            // Émettre un événement personnalisé quand les paramètres Canvas sont sauvegardés
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.querySelector('[name="submit_canvas"]')) {
                    // Ajouter un délai pour permettre à WordPress de traiter la soumission
                    setTimeout(function() {
                        // Déclencher l'événement personnalisé pour notifier React
                        window.dispatchEvent(new Event('pdfBuilderCanvasSettingsUpdated'));
                    }, 500);
                }
            });

            // ============================================================
            // Gestion du Mode Développeur - Affiche/cache les sections
            // ============================================================
            jQuery(document).ready(function() {
                const developerCheckbox = jQuery('#developer_enabled');
                const licenseSectionDiv = jQuery('#dev-license-section');
                const debugSectionDiv = jQuery('#dev-debug-section');
                const logsSectionDiv = jQuery('#dev-logs-section');
                const logsViewerSectionDiv = jQuery('#dev-logs-viewer-section');
                const toolsSectionDiv = jQuery('#dev-tools-section');
                const shortcutsSectionDiv = jQuery('#dev-shortcuts-section');
                const consoleSectionDiv = jQuery('#dev-console-section');
                const hooksSectionDiv = jQuery('#dev-hooks-section');
                const optimizationsSectionDiv = jQuery('#dev-optimizations-section');
                const developerPasswordField = jQuery('#developer_password');
                const developerPasswordToggle = jQuery('#toggle_password');

                // Fonction pour mettre à jour la visibilité
                function updateDeveloperSectionsVisibility() {
                    const isDeveloperEnabled = developerCheckbox.is(':checked');
                    const displayStyle = isDeveloperEnabled ? 'block' : 'none';

                    licenseSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    debugSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    logsSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    logsViewerSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    toolsSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    shortcutsSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    consoleSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    hooksSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });
                    optimizationsSectionDiv.fadeToggle(200, function() {
                        jQuery(this).css('display', displayStyle);
                    });

                    // Griser/dégriser le champ mot de passe développeur
                    if (isDeveloperEnabled) {
                        developerPasswordField.prop('disabled', false).css('opacity', '1');
                        developerPasswordToggle.prop('disabled', false).css('opacity', '1');
                    } else {
                        developerPasswordField.prop('disabled', true).css('opacity', '0.5');
                        developerPasswordToggle.prop('disabled', true).css('opacity', '0.5');
                    }
                }

                // Ajouter l'event listener au checkbox
                developerCheckbox.on('change', function() {
                    updateDeveloperSectionsVisibility();
                });

                // Initialiser l'état au chargement de la page
                updateDeveloperSectionsVisibility();
            });
    </script>

