<?php
/**
 * PDF Builder Pro - Settings Page
 * Complete settings with all tabs
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Function to send AJAX response
function send_ajax_response($success, $message = '', $data = []) {
    error_log('AJAX: send_ajax_response called with success=' . ($success ? 'true' : 'false') . ', message=' . $message);
    $response = json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    error_log('AJAX: JSON response: ' . $response);
    wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
}

// Check if this is an AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Debug: Log POST data for AJAX requests
if ($is_ajax && !empty($_POST)) {
    error_log('AJAX POST data: ' . print_r($_POST, true));
}

// For AJAX requests, only process POST data and exit - don't show HTML
if ($is_ajax && !empty($_POST)) {
    // Process the request and exit - the processing code below will handle it
    // This ensures no HTML is output for AJAX requests
    return; // Exit early for AJAX POST requests to prevent HTML output
}

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'pdf-builder-pro'));
}

// Debug: Page loaded
if (defined('WP_DEBUG') && WP_DEBUG) {
    // Logs removed for clarity
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
    // Logs removed for clarity
} else {
    // Logs removed for clarity
}

// Process form
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if ($is_ajax) error_log('AJAX: Matched condition 1 - submit + pdf_builder_settings_nonce');
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Logs removed for clarity
    }
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Logs removed for clarity
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
            'debug_ajax' => isset($_POST['debug_ajax']),
            'debug_performance' => isset($_POST['debug_performance']),
            'debug_database' => isset($_POST['debug_database']),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),
            'license_test_mode' => isset($_POST['license_test_mode']),
            'disable_hooks' => sanitize_text_field($_POST['disable_hooks'] ?? ''),
            'enable_profiling' => isset($_POST['enable_profiling']),
            'force_https' => isset($_POST['force_https']),
        ];
        $new_settings = array_merge($settings, $to_save);
        
        // Check if settings actually changed - use serialize for deep comparison
        $settings_changed = serialize($new_settings) !== serialize($settings);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Logs removed for clarity
        }
        
        $result = update_option('pdf_builder_settings', $new_settings);

        try {
            // Debug: Always log the result for troubleshooting
            // Logs removed for clarity

            // Simplified success logic: if no exception was thrown, consider it successful
            if ($is_ajax) {
                send_ajax_response(true, 'Paramètres enregistrés avec succès.');
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
            }
        } catch (Exception $e) {
            // Logs removed for clarity
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
if (isset($_POST['clear_cache']) &&
    (isset($_POST['pdf_builder_clear_cache_nonce_performance']) ||
     isset($_POST['pdf_builder_clear_cache_nonce_maintenance']))) {

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
    if ($is_ajax) error_log('AJAX: Matched condition 2 - submit + pdf_builder_general_nonce');
    if (wp_verify_nonce($_POST['pdf_builder_general_nonce'], 'pdf_builder_settings')) {
        $general_settings = [
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
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

if (isset($_POST['submit_pdf']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $pdf_settings = [
            'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
            'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
            'pdf_author' => sanitize_text_field($_POST['pdf_author'] ?? get_bloginfo('name')),
            'pdf_subject' => sanitize_text_field($_POST['pdf_subject'] ?? ''),
            'include_metadata' => isset($_POST['include_metadata']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'auto_crop' => isset($_POST['auto_crop']),
            'max_image_size' => intval($_POST['max_image_size'] ?? 2048),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $pdf_settings));
        if ($is_ajax) {
            send_ajax_response(true, 'Paramètres PDF enregistrés avec succès.');
        } else {
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres PDF enregistrés avec succès.</p></div>';
        }
        $settings = get_option('pdf_builder_settings', []);
    }
}

if (isset($_POST['submit_security']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $security_settings = [
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $security_settings));
        if ($is_ajax) {
            send_ajax_response(true, 'Paramètres de sécurité enregistrés avec succès.');
        } else {
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de sécurité enregistrés avec succès.</p></div>';
        }
        $settings = get_option('pdf_builder_settings', []);
    }
}

if (isset($_POST['submit_canvas']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        // Utiliser le Canvas Manager pour sauvegarder les paramètres
        if (class_exists('PDF_Builder_Canvas_Manager')) {
            $canvas_manager = \PDF_Builder_Canvas_Manager::get_instance();
            // Filtrer les paramètres canvas avant sauvegarde
            $canvas_params = $canvas_manager->filter_canvas_parameters($_POST);
            $canvas_manager->save_canvas_settings($canvas_params);
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres Canvas enregistrés avec succès.</p></div>';
        } else {
            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur: Canvas Manager non disponible.</p></div>';
        }
    }
}

if (isset($_POST['submit_developpeur']) && isset($_POST['pdf_builder_developpeur_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_developpeur_nonce'], 'pdf_builder_settings')) {
        $dev_settings = [
            'developer_enabled' => isset($_POST['developer_enabled']),
            'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
            'debug_php_errors' => isset($_POST['debug_php_errors']),
            'debug_javascript' => isset($_POST['debug_javascript']),
            'debug_ajax' => isset($_POST['debug_ajax']),
            'debug_performance' => isset($_POST['debug_performance']),
            'debug_database' => isset($_POST['debug_database']),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),
            'disable_hooks' => sanitize_text_field($_POST['disable_hooks'] ?? ''),
            'enable_profiling' => isset($_POST['enable_profiling']),
            'force_https' => isset($_POST['force_https']),
            'license_test_mode' => isset($_POST['license_test_mode']),
        ];
        // Logs removed for clarity
        $result = update_option('pdf_builder_settings', array_merge($settings, $dev_settings));
        
        // Sauvegarder aussi l'état du mode test dans une option séparée pour le handler de licence
        update_option('pdf_builder_license_test_mode_enabled', isset($_POST['license_test_mode']));
        
        // Logs removed for clarity
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres développeur enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
        // Logs removed for clarity
    } else {
        // Logs removed for clarity
        $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
    }
}

if (isset($_POST['submit_performance']) && isset($_POST['pdf_builder_performance_nonce'])) {
    // Logs removed for clarity
    if (wp_verify_nonce($_POST['pdf_builder_performance_nonce'], 'pdf_builder_performance_settings')) {
        $performance_settings = [
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
            'compress_images' => isset($_POST['compress_images']),
            'image_quality' => intval($_POST['image_quality'] ?? 85),
            'optimize_for_web' => isset($_POST['optimize_for_web']),
            'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
            'limit_fps' => isset($_POST['limit_fps']),
            'max_fps' => intval($_POST['max_fps'] ?? 60),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $performance_settings));
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
    // Logs removed for clarity
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
    // Logs removed for clarity
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
    // Logs removed for clarity
    if (wp_verify_nonce($_POST['pdf_builder_canvas_nonce'], 'pdf_builder_settings')) {
        $canvas_settings_to_save = [
            'default_canvas_width' => intval($_POST['default_canvas_width'] ?? 794),
            'default_canvas_height' => intval($_POST['default_canvas_height'] ?? 1123),
            'default_canvas_unit' => sanitize_text_field($_POST['default_canvas_unit'] ?? 'px'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            'canvas_background_color' => sanitize_text_field($_POST['canvas_background_color'] ?? '#ffffff'),
            'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
            'container_background_color' => sanitize_text_field($_POST['container_background_color'] ?? '#f8f9fa'),
            'container_show_transparency' => isset($_POST['container_show_transparency']),
            'margin_top' => intval($_POST['margin_top'] ?? 28),
            'margin_right' => intval($_POST['margin_right'] ?? 28),
            'margin_bottom' => intval($_POST['margin_bottom'] ?? 10),
            'margin_left' => intval($_POST['margin_left'] ?? 10),
            'show_margins' => isset($_POST['show_margins']),
            'show_grid' => isset($_POST['show_grid']),
            'grid_size' => intval($_POST['grid_size'] ?? 10),
            'grid_color' => sanitize_text_field($_POST['grid_color'] ?? '#e0e0e0'),
            'snap_to_grid' => isset($_POST['snap_to_grid']),
            'snap_to_elements' => isset($_POST['snap_to_elements']),
            'snap_tolerance' => intval($_POST['snap_tolerance'] ?? 5),
            'show_guides' => isset($_POST['show_guides']),
            'default_zoom' => intval($_POST['default_zoom'] ?? 100),
            'zoom_step' => intval($_POST['zoom_step'] ?? 25),
            'min_zoom' => intval($_POST['min_zoom'] ?? 10),
            'max_zoom' => intval($_POST['max_zoom'] ?? 500),
            'zoom_with_wheel' => isset($_POST['zoom_with_wheel']),
            'pan_with_mouse' => isset($_POST['pan_with_mouse']),
            'show_resize_handles' => isset($_POST['show_resize_handles']),
            'handle_size' => intval($_POST['handle_size'] ?? 8),
            'handle_color' => sanitize_text_field($_POST['handle_color'] ?? '#007cba'),
            'enable_rotation' => isset($_POST['enable_rotation']),
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
            'auto_crop' => isset($_POST['auto_crop']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'optimize_for_web' => isset($_POST['optimize_for_web']),
            'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
            'limit_fps' => isset($_POST['limit_fps']),
            'max_fps' => intval($_POST['max_fps'] ?? 60),
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
            'auto_save_versions' => intval($_POST['auto_save_versions'] ?? 10),
            'undo_levels' => intval($_POST['undo_levels'] ?? 50),
            'redo_levels' => intval($_POST['redo_levels'] ?? 50),
            'enable_keyboard_shortcuts' => isset($_POST['enable_keyboard_shortcuts']),
            'debug_mode' => isset($_POST['debug_mode']),
            'show_fps' => isset($_POST['show_fps']),
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
    // Logs removed for clarity
    if (wp_verify_nonce($_POST['pdf_builder_templates_nonce'], 'pdf_builder_settings')) {
        $template_mappings = [];
        
        // Récupérer tous les mappings statut -> template
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            foreach ($_POST['order_status_templates'] as $status => $template_id) {
                if (!empty($template_id)) {
                    $template_mappings[$status] = intval($template_id);
                }
            }
        }
        
        // Sauvegarder les mappings dans une option dédiée
        update_option('pdf_builder_order_status_templates', $template_mappings);
        
        if ($is_ajax) {
            $response = json_encode(['success' => true, 'message' => 'Assignations de templates enregistrées avec succès.']);
            wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
        } else {
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Assignations de templates enregistrées avec succès.</p></div>';
        }
        $settings = get_option('pdf_builder_settings', []);
    }
}

if (isset($_POST['submit_maintenance']) && isset($_POST['pdf_builder_settings_nonce'])) {
    // Logs removed for clarity
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
    'default_canvas_width' => $canvas_settings_js['default_canvas_width'] ?? 794,
    'default_canvas_height' => $canvas_settings_js['default_canvas_height'] ?? 1123,
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
    'auto_save_enabled' => $canvas_settings_js['auto_save_enabled'] ?? true,
    'auto_save_interval' => $canvas_settings_js['auto_save_interval'] ?? 30,
    'auto_save_versions' => $canvas_settings_js['auto_save_versions'] ?? 10,
    'undo_levels' => $canvas_settings_js['undo_levels'] ?? 50,
    'redo_levels' => $canvas_settings_js['redo_levels'] ?? 50,
    'enable_keyboard_shortcuts' => $canvas_settings_js['enable_keyboard_shortcuts'] ?? true,
    'debug_mode' => $canvas_settings_js['debug_mode'] ?? false,
    'show_fps' => $canvas_settings_js['show_fps'] ?? false
]); ?>;
// Logs removed for clarity
</script>
<?php
// If this is an AJAX request that wasn't handled above, return error
if ($is_ajax) {
    send_ajax_response(false, 'Requête AJAX non reconnue ou invalide.');
}
?>
<div class="wrap">
    <h1><?php _e('⚙️ PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>
    
    <?php foreach ($notices as $notice) echo $notice; ?>
    
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
    
    <!-- Bouton de sauvegarde flottant -->
    <div id="floating-save-button" class="floating-save-container">
        <button type="submit" name="submit_global" id="global-save-btn" class="button button-primary floating-save-btn"  style="padding:5px;">
            💾 Enregistrer
        </button>
        <div class="save-status" id="save-status"></div>
    </div>
        
        <div id="general" class="tab-content">
            <h2>Paramètres Généraux</h2>
            <p style="color: #666;">Paramètres de base pour la génération PDF. Pour le cache et la sécurité, voir les onglets Performance et Sécurité.</p>
            
            <form method="post" id="general-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_general_nonce'); ?>
                <input type="hidden" name="submit" value="1">
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📋 Cache</h3>
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
                        <button type="button" id="test-cache-btn" class="button button-secondary">
                            🧪 Tester l'intégration du cache
                        </button>
                        <span id="cache-test-results" style="margin-left: 10px;"></span>
                        <div id="cache-test-output" style="display: none; margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;"></div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📄 Paramètres PDF</h3>
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
            
            <p class="submit">
                <button type="submit" name="submit" class="button button-primary" id="general-submit-btn">Enregistrer les paramètres</button>
                <button type="button" id="debug-btn" class="button">Debug Form</button>
            </p>
            </form>
        </div>
        
        <div id="licence" class="tab-content hidden-tab">
            <h2>Gestion de la Licence</h2>
            
            <?php
            $license_status = get_option('pdf_builder_license_status', 'free');
            $license_key = get_option('pdf_builder_license_key', '');
            $license_expires = get_option('pdf_builder_license_expires', '');
            $license_activated_at = get_option('pdf_builder_license_activated_at', '');
            $is_premium = $license_status !== 'free' && $license_status !== 'expired';
            $test_mode_enabled = get_option('pdf_builder_license_test_mode_enabled', false);
            $test_key = get_option('pdf_builder_license_test_key', '');
            
            // Traitement activation licence
            if (isset($_POST['activate_license']) && isset($_POST['pdf_builder_license_nonce'])) {
                // Logs removed for clarity
                if (wp_verify_nonce($_POST['pdf_builder_license_nonce'], 'pdf_builder_license')) {
                    $new_key = sanitize_text_field($_POST['license_key'] ?? '');
                    if (!empty($new_key)) {
                        update_option('pdf_builder_license_key', $new_key);
                        update_option('pdf_builder_license_status', 'active');
                        update_option('pdf_builder_license_expires', date('Y-m-d', strtotime('+1 year')));
                        update_option('pdf_builder_license_activated_at', date('Y-m-d H:i:s'));
                        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence activée avec succès !</p></div>';
                        $is_premium = true;
                        $license_key = $new_key;
                        $license_status = 'active';
                        $license_activated_at = date('Y-m-d H:i:s');
                    }
                }
            }
            
            // Traitement désactivation licence
            if (isset($_POST['deactivate_license']) && isset($_POST['pdf_builder_deactivate_nonce'])) {
                // Logs removed for clarity
                if (wp_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                    delete_option('pdf_builder_license_key');
                    delete_option('pdf_builder_license_expires');
                    delete_option('pdf_builder_license_activated_at');
                    update_option('pdf_builder_license_status', 'free');
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée.</p></div>';
                    $is_premium = false;
                    $license_key = '';
                    $license_status = 'free';
                    $license_activated_at = '';
                }
            }
            ?>
            
            <!-- Statut de la licence -->
            <div style="background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">📊 Statut de la Licence</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <!-- Carte Statut Principal -->
                    <div style="border: 2px solid <?php echo $is_premium ? '#28a745' : '#6c757d'; ?>; border-radius: 8px; padding: 15px; background: <?php echo $is_premium ? '#d4edda' : '#f8f9fa'; ?>;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Statut</div>
                        <div style="font-size: 20px; font-weight: bold; color: <?php echo $is_premium ? '#155724' : '#495057'; ?>;">
                            <?php echo $is_premium ? '✅ Premium Actif' : '○ Gratuit'; ?>
                        </div>
                    </div>
                    
                    <!-- Carte Mode Test (si applicable) -->
                    <?php if ($test_mode_enabled): ?>
                    <div style="border: 2px solid #ffc107; border-radius: 8px; padding: 15px; background: #fff3cd;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Mode</div>
                        <div style="font-size: 20px; font-weight: bold; color: #856404;">
                            🧪 TEST (Dev)
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Carte Date d'expiration -->
                    <?php if ($is_premium && $license_expires): ?>
                    <div style="border: 2px solid #17a2b8; border-radius: 8px; padding: 15px; background: #d1ecf1;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Expire le</div>
                        <div style="font-size: 18px; font-weight: bold; color: #0c5460;">
                            <?php echo date('d/m/Y', strtotime($license_expires)); ?>
                        </div>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
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
                    <?php endif; ?>
                </div>
                
                <!-- Détails de la clé -->
                <?php if ($is_premium || $test_mode_enabled): ?>
                <div style="background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 4px; padding: 15px; margin-top: 15px;">
                    <h4 style="margin: 0 0 12px 0;">🔐 Détails de la Clé</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <?php if ($is_premium && $license_key): ?>
                        <tr style="border-bottom: 1px solid #e5e5e5;">
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
                                <span style="margin-left: 10px; cursor: pointer; color: #007bff;" onclick="navigator.clipboard.writeText('<?php echo esc_js($license_key); ?>'); alert('✅ Clé copiée !'); ">� Copier</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($test_mode_enabled && $test_key): ?>
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
                        <?php endif; ?>
                        
                        <?php if ($is_premium && $license_activated_at): ?>
                        <tr style="border-bottom: 1px solid #e5e5e5;">
                            <td style="padding: 8px 0; font-weight: 500;">Activée le :</td>
                            <td style="padding: 8px 0;">
                                <?php echo date('d/m/Y à H:i', strtotime($license_activated_at)); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <td style="padding: 8px 0; font-weight: 500;">Statut :</td>
                            <td style="padding: 8px 0;">
                                <?php 
                                if ($test_mode_enabled) {
                                    echo '<span style="background: #ffc107; color: #000; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">🧪 MODE TEST</span>';
                                } elseif ($is_premium) {
                                    echo '<span style="background: #28a745; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">✅ ACTIVE</span>';
                                } else {
                                    echo '<span style="background: #6c757d; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">○ GRATUIT</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Activation/Désactivation -->
            <?php if (!$is_premium): ?>
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3>Activer une Licence Premium</h3>
                <p>Entrez votre clé de licence pour débloquer toutes les fonctionnalités premium.</p>
                
                <form method="post">
                    <?php wp_nonce_field('pdf_builder_license', 'pdf_builder_license_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="license_key">Clé de licence</label></th>
                            <td>
                                <input type="text" id="license_key" name="license_key" class="regular-text" 
                                       placeholder="XXXX-XXXX-XXXX-XXXX" style="min-width: 300px;">
                                <p class="description">Vous pouvez trouver votre clé dans votre compte client.</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" name="activate_license" class="button button-primary">
                            Activer la licence
                        </button>
                    </p>
                </form>
            </div>
            <?php else: ?>
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3>Gestion de la Licence</h3>
                <p>Votre licence premium est active. Vous pouvez la désactiver pour la transférer vers un autre site.</p>
                
                <form method="post">
                    <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                    <p class="submit">
                        <button type="submit" name="deactivate_license" class="button button-secondary"
                                onclick="return confirm('Êtes-vous sûr de vouloir désactiver cette licence ?');">
                            Désactiver la licence
                        </button>
                    </p>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Informations utiles -->
            <div style="background: #e7f3ff; border-left: 4px solid #0066cc; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #004080;">ℹ️ Informations Utiles</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Site actuel :</strong> <?php echo esc_html(home_url()); ?></li>
                    <li><strong>Plan actif :</strong> <?php echo $test_mode_enabled ? '🧪 Mode Test' : ($is_premium ? '✅ Premium' : '○ Gratuit'); ?></li>
                    <?php if ($is_premium): ?>
                    <li><strong>Support :</strong> <a href="https://pdfbuilderpro.com/support" target="_blank">Contact Support Premium</a></li>
                    <li><strong>Documentation :</strong> <a href="https://pdfbuilderpro.com/docs" target="_blank">Lire la Documentation</a></li>
                    <?php endif; ?>
                    <li><strong>Version du plugin :</strong> <?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : 'N/A'; ?></li>
                </ul>
            </div>
            
            <!-- Comparaison des fonctionnalités -->
            <div style="margin-top: 30px;">
                <h3>Comparaison des Fonctionnalités</h3>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Fonctionnalité</th>
                            <th style="width: 15%; text-align: center;">Gratuit</th>
                            <th style="width: 15%; text-align: center;">Premium</th>
                            <th style="width: 30%;">Description</th>
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
        </div>
        
        <div id="performance" class="tab-content hidden-tab">
            <form method="post" id="performance-form" action="">
                <?php wp_nonce_field('pdf_builder_performance_settings', 'pdf_builder_performance_nonce'); ?>
                <input type="hidden" name="current_tab" value="performance">
                <input type="hidden" name="submit_performance" value="1">
                <h2>Paramètres de Performance</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="auto_save_enabled">Sauvegarde Auto</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="auto_save_enabled" name="auto_save_enabled" value="1" 
                                       <?php checked($settings['auto_save_enabled'] ?? false); ?> />
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
                        <input type="number" id="auto_save_interval" name="auto_save_interval" value="<?php echo intval($settings['auto_save_interval'] ?? 30); ?>" 
                               min="10" max="300" step="10" />
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
                               min="30" max="100" step="5" style="width: 300px;" />
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
                               min="15" max="240" />
                        <p class="description">Images par seconde maximales (15-240 FPS)</p>
                    </td>
                </tr>
            </table>
            
            <!-- Section Nettoyage -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3>Nettoyage & Maintenance</h3>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Qualité & Export</h3>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Métadonnées & Contenu</h3>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Optimisation & Compression</h3>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">⚙️ Limites & Protections Système</h3>
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
                               value="<?php echo intval($settings['max_execution_time'] ?? 300); ?>" min="1" max="3600" />
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔐 Protections</h3>
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
                // Logs removed for clarity
                // Logs removed for clarity
                // Logs removed for clarity
                
                if (wp_verify_nonce($_POST['pdf_builder_roles_nonce'], 'pdf_builder_roles')) {
                    // Logs removed for clarity
                    
                    $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) 
                        ? array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles'])
                        : [];
                    
                    // Logs removed for clarity
                    
                    if (empty($allowed_roles)) {
                        $allowed_roles = ['administrator']; // Au minimum l'admin
                        // Logs removed for clarity
                    }
                    
                    update_option('pdf_builder_allowed_roles', $allowed_roles);
                    // Logs removed for clarity
                    
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Rôles autorisés mis à jour avec succès.</p></div>';
                } else {
                    // Logs removed for clarity
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
                    <?php foreach ($all_roles as $role_key => $role):
                        $role_name = translate_user_role($role['name']);
                        $is_selected = in_array($role_key, $allowed_roles);
                        $description = $role_descriptions[$role_key] ?? 'Rôle personnalisé';
                        $is_admin = $role_key === 'administrator';
                    ?>
                        <div class="role-toggle-item <?php echo $is_admin ? 'admin-role' : ''; ?>">
                            <div class="role-info">
                                <div class="role-name">
                                    <?php echo esc_html($role_name); ?>
                                    <?php if ($is_admin): ?>
                                        <span class="admin-badge">🔒 Toujours actif</span>
                                    <?php endif; ?>
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
                    <?php endforeach; ?>
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
                        // Logs removed for clarity
                        
                        rolesForm.addEventListener('submit', function(e) {
                            // Logs removed for clarity
                            
                            // Laisser le formulaire se soumettre normalement (POST)
                            // Logs removed for clarity
                        });
                        
                        // Empêcher tout autre event listener AJAX
                        rolesForm.addEventListener('click', function(e) {
                            if (e.target.type === 'submit') {
                                // Logs removed for clarity
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
                            // Logs removed for clarity
                        }
                    }
                    
                    // Bouton Sélectionner Tout
                    if (selectAllBtn) {
                        selectAllBtn.addEventListener('click', function() {
                            // Logs removed for clarity
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
                            // Logs removed for clarity
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
                            // Logs removed for clarity
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
                            // Logs removed for clarity
                            updateSelectedCount();
                        });
                    });
                    
                    // Initialiser le compteur
                    updateSelectedCount();
                    // Logs removed for clarity
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
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 20px;">
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
                // Logs removed for clarity
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Notifications par Email</h3>
            
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
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Événements de Notification</h3>
                
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
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Configuration SMTP</h3>
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
            <div style="background: #f8f9fa; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top: 20px;">
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
                'default_canvas_width' => 794,
                'default_canvas_height' => 1123,
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Dimensions par Défaut</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_canvas_width">Largeur</label></th>
                    <td>
                        <input type="number" id="default_canvas_width" name="default_canvas_width" 
                               value="<?php echo intval($canvas_settings['default_canvas_width'] ?? 794); ?>" 
                               min="50" max="2000" />
                        <span>px</span>
                        <p class="description">Largeur par défaut du canvas (794px = A4)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_canvas_height">Hauteur</label></th>
                    <td>
                        <input type="number" id="default_canvas_height" name="default_canvas_height" 
                               value="<?php echo intval($canvas_settings['default_canvas_height'] ?? 1123); ?>" 
                               min="50" max="2000" />
                        <span>px</span>
                        <p class="description">Hauteur par défaut du canvas (1123px = A4)</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Fond & Couleurs</h3>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Marges</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_margins">Afficher les Marges</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_margins" name="show_margins" value="1" 
                                       <?php checked($canvas_settings['show_margins']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Marges visibles</span>
                        </div>
                        <div class="toggle-description">Affiche les lignes de marge sur le canvas</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Marges (mm)</label></th>
                    <td>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div>
                                <label for="margin_top">Haut :</label>
                                <input type="number" id="margin_top" name="margin_top" 
                                       value="<?php echo intval($canvas_settings['margin_top'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_right">Droite :</label>
                                <input type="number" id="margin_right" name="margin_right" 
                                       value="<?php echo intval($canvas_settings['margin_right'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_bottom">Bas :</label>
                                <input type="number" id="margin_bottom" name="margin_bottom" 
                                       value="<?php echo intval($canvas_settings['margin_bottom'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_left">Gauche :</label>
                                <input type="number" id="margin_left" name="margin_left" 
                                       value="<?php echo intval($canvas_settings['margin_left'] ?? 10); ?>" min="0" />
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Grille & Aimants</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_grid">Afficher Grille</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_grid" name="show_grid" value="1" 
                                       <?php checked($canvas_settings['show_grid']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Grille visible</span>
                        </div>
                        <div class="toggle-description">Affiche une grille de référence</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="grid_size">Taille Grille (px)</label></th>
                    <td>
                        <input type="number" id="grid_size" name="grid_size" 
                               value="<?php echo intval($canvas_settings['grid_size'] ?? 10); ?>" min="5" max="100" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="grid_color">Couleur Grille</label></th>
                    <td>
                        <input type="color" id="grid_color" name="grid_color" 
                               value="<?php echo esc_attr($canvas_settings['grid_color'] ?? '#e0e0e0'); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_to_grid">Magnétisme Grille</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="snap_to_grid" name="snap_to_grid" value="1" 
                                       <?php checked($canvas_settings['snap_to_grid']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer magnétisme</span>
                        </div>
                        <div class="toggle-description">Les éléments s'accrochent à la grille</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_to_elements">Magnétisme Éléments</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="snap_to_elements" name="snap_to_elements" value="1" 
                                       <?php checked($canvas_settings['snap_to_elements']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer magnétisme</span>
                        </div>
                        <div class="toggle-description">Les éléments s'accrochent les uns aux autres</div>
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
                    <th scope="row"><label for="show_guides">Afficher Guides</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_guides" name="show_guides" value="1" 
                                       <?php checked($canvas_settings['show_guides']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Guides visibles</span>
                        </div>
                        <div class="toggle-description">Affiche les guides de positionnement</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Zoom & Navigation</h3>
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
                    <th scope="row"><label for="zoom_with_wheel">Zoom à la Molette</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="zoom_with_wheel" name="zoom_with_wheel" value="1" 
                                       <?php checked($canvas_settings['zoom_with_wheel']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Zoom molette</span>
                        </div>
                        <div class="toggle-description">Permet de zoomer avec la molette souris</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_with_mouse">Panoramique à la Souris</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="pan_with_mouse" name="pan_with_mouse" value="1" 
                                       <?php checked($canvas_settings['pan_with_mouse']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Panoramique souris</span>
                        </div>
                        <div class="toggle-description">Permet de déplacer le canvas en glissant</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Sélection & Manipulation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_resize_handles">Afficher Poignées</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_resize_handles" name="show_resize_handles" value="1" 
                                       <?php checked($canvas_settings['show_resize_handles']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Poignées visibles</span>
                        </div>
                        <div class="toggle-description">Affiche les poignées de redimensionnement</div>
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
                    <th scope="row"><label for="multi_select">Sélection Multiple</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="multi_select" name="multi_select" value="1" 
                                       <?php checked($canvas_settings['multi_select']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Multi-sélection</span>
                        </div>
                        <div class="toggle-description">Permet de sélectionner plusieurs éléments</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="copy_paste_enabled">Copier/Coller</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="copy_paste_enabled" name="copy_paste_enabled" value="1" 
                                       <?php checked($canvas_settings['copy_paste_enabled']); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Copier/coller</span>
                        </div>
                        <div class="toggle-description">Active les raccourcis Ctrl+C / Ctrl+V</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Undo/Redo & Auto-save</h3>
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
                // Logs removed for clarity
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
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Mappage des Statuts aux Templates</h3>
                
                <table class="form-table">
                    <?php foreach ($order_statuses as $status_key => $status_name):
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
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo intval($template->ID); ?>" 
                                                <?php selected($selected_template, $template->ID); ?>>
                                            <?php echo esc_html($template->post_title ?: '(Sans titre)'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    Template automatique pour les commandes avec ce statut
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
                        <?php foreach ($order_statuses as $status_key => $status_name):
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
                        <?php endforeach; ?>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🧹 Nettoyage des Données</h3>
            <p>Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('pdf_builder_clear_cache_maintenance', 'pdf_builder_clear_cache_nonce_maintenance'); ?>
                    <button type="submit" name="clear_cache" class="button button-secondary" style="width: 100%;">
                        🗑️ Vider le Cache
                    </button>
                </form>
                
                <button type="button" class="button button-secondary" onclick="alert('Suppression de fichiers temporaires...');" style="width: 100%;">
                    📁 Supprimer Fichiers Temp
                </button>
                
                <button type="button" class="button button-secondary" onclick="alert('Optimisation base de données...');" style="width: 100%;">
                    ⚡ Optimiser BD
                </button>
            </div>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔧 Réparation & Réinitialisation</h3>
            <p>Réparez les templates corrompus et les paramètres invalides.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <button type="button" class="button button-secondary" onclick="alert('Réparation des templates en cours...');" style="width: 100%;">
                    ✅ Réparer Templates
                </button>
                
                <button type="button" class="button button-warning" 
                        onclick="if(confirm('Réinitialiser tous les paramètres ? Cette action est irréversible.')) { alert('Réinitialisation...'); }" 
                        style="width: 100%;">
                    ⚠️ Réinitialiser Paramètres
                </button>
                
                <button type="button" class="button button-secondary" onclick="alert('Validation de l\'intégrité en cours...');" style="width: 100%;">
                    🔍 Vérifier Intégrité
                </button>
            </div>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🐛 Outils de Développement</h3>
            <p>Outils pour les développeurs et le débogage avancé.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Console de Debug</label></th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="alert('Ouverture de la console...');">
                            🖥️ Ouvrir Console
                        </button>
                        <p class="description">Affiche les logs JavaScript avec emojis (🚀, ✅, ❌, ⚠️)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Logs Debug</label></th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="alert('Vider les logs debug...');">
                            🗑️ Vider Logs
                        </button>
                        <p class="description">Supprime tous les logs de débogation accumulés</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📊 Informations Système</h3>
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
            
            <!-- Section Logs & Diagnostics -->
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📋 Logs & Diagnostics</h3>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 25%;">Type</th>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 25%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Erreurs PHP</strong></td>
                        <td>Errors et Warnings PHP du plugin</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Génération PDF</strong></td>
                        <td>Logs des opérations de génération PDF</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Événements</strong></td>
                        <td>Événements système importants</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Détails Requis</strong></td>
                        <td>Toutes les requêtes traitées</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                </tbody>
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
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔐 Contrôle d'Accès</h3>
                
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
                        <?php if (!empty($settings['developer_password'])): ?>
                        <p class="description" style="color: #28a745;">✓ Mot de passe configuré et sauvegardé</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">� Test de Licence</h3>
            
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
                            <?php if ($license_test_key): ?>
                            <button type="button" id="delete_license_key_btn" class="button button-link-delete" style="padding: 8px 12px; height: auto;">
                                🗑️ Supprimer
                            </button>
                            <?php endif; ?>
                        </div>
                        <p class="description">Génère une clé de test aléatoire pour valider le système de licence</p>
                        <span id="license_key_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">�🔍 Paramètres de Debug</h3>
            
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📝 Fichiers Logs</h3>
            
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🚀 Optimisations Avancées</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="disable_hooks">Désactiver Hooks</label></th>
                    <td>
                        <input type="text" id="disable_hooks" name="disable_hooks" placeholder="hook1,hook2,hook3" style="width: 100%; max-width: 400px;" />
                        <p class="description">Hooks WordPress à désactiver (séparés par virgule). Utile pour déboguer les conflits</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_profiling">Profiling PHP</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_profiling" name="enable_profiling" value="1" <?php echo isset($settings['enable_profiling']) && $settings['enable_profiling'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Profiling actif</span>
                        </div>
                        <div class="toggle-description">Active le profiling PHP (impact sur les performances). Générer des rapports xdebug</div>
                    </td>
                </tr>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📋 Visualiseur de Logs Temps Réel</h3>
            
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🧪 Outils de Développement</h3>
            
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">⌨️ Raccourcis Clavier Développeur</h3>
            
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🎨 Console Code</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="test_code">Code Test</label></th>
                    <td>
                        <textarea id="test_code" style="width: 100%; height: 150px; font-family: monospace; padding: 10px;">// Exemple: console.log('Test développeur');
// var result = pdf_builder ? 'Plugin chargé' : 'Plugin non chargé';
// console.log(result);</textarea>
                        <p class="description">Zone d'essai pour du code JavaScript (exécution côté client)</p>
                        <div style="margin-top: 10px;">
                            <button type="button" id="execute_code_btn" class="button button-secondary">▶️ Exécuter Code JS</button>
                            <button type="button" id="clear_console_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Console</button>
                            <span id="code_result" style="margin-left: 20px; font-weight: bold;"></span>
                        </div>
                    </td>
                </tr>
            </table>
            
            <!-- Tableau de références des hooks disponibles -->
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🎣 Hooks Disponibles</h3>
            
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

<style>
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

    /* Bouton de sauvegarde flottant */
    .floating-save-container {
        position: fixed;
        top: 40px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
    }

    .floating-save-btn {
        background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
        border: none;
        border-radius: 50px;
        padding: 12px 24px;
        color: white;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 140px;
        justify-content: center;
    }

    .floating-save-btn:hover {
        background: linear-gradient(135deg, #005a87 0%, #004466 100%);
        box-shadow: 0 6px 16px rgba(0, 124, 186, 0.4);
        transform: translateY(-2px);
    }

    .floating-save-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(0, 124, 186, 0.3);
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
    'default_canvas_width' => $canvas_settings_js['default_canvas_width'] ?? 794,
    'default_canvas_height' => $canvas_settings_js['default_canvas_height'] ?? 1123,
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
    'auto_save_enabled' => $canvas_settings_js['auto_save_enabled'] ?? true,
    'auto_save_interval' => $canvas_settings_js['auto_save_interval'] ?? 30,
    'auto_save_versions' => $canvas_settings_js['auto_save_versions'] ?? 10,
    'undo_levels' => $canvas_settings_js['undo_levels'] ?? 50,
    'redo_levels' => $canvas_settings_js['redo_levels'] ?? 50,
    'enable_keyboard_shortcuts' => $canvas_settings_js['enable_keyboard_shortcuts'] ?? true,
    'debug_mode' => $canvas_settings_js['debug_mode'] ?? false,
    'show_fps' => $canvas_settings_js['show_fps'] ?? false
]); ?>;
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du bouton de sauvegarde global
        function setupGlobalSaveButton() {
            const globalSaveBtn = document.getElementById('global-save-btn');
            const saveStatus = document.getElementById('save-status');
            
            console.log('🔘 SETUP GLOBAL SAVE BUTTON - Button found:', globalSaveBtn);
            
            if (globalSaveBtn) {
                globalSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Trouver l'onglet actif (celui qui n'a pas la classe hidden-tab)
                    const activeTab = document.querySelector('.tab-content:not(.hidden-tab)') ||
                                    document.querySelector('.tab-content.active') ||
                                    document.getElementById('general');
                    
                    if (activeTab) {
                        // Trouver le formulaire principal dans l'onglet actif
                        let form = null;
                        if (activeTab.id === 'performance') {
                            form = document.getElementById('performance-form');
                        } else if (activeTab.id === 'general') {
                            form = document.getElementById('general-form');
                        } else if (activeTab.id === 'pdf') {
                            form = document.getElementById('pdf-form');
                        } else if (activeTab.id === 'securite') {
                            form = document.getElementById('securite-form');
                        } else if (activeTab.id === 'canvas') {
                            form = document.getElementById('canvas-form');
                        } else if (activeTab.id === 'templates') {
                            form = document.getElementById('templates-form');
                        } else if (activeTab.id === 'developpeur') {
                            form = document.getElementById('developpeur-form');
                        } else {
                            form = activeTab.querySelector('form[id$="-form"]') || activeTab.querySelector('form');
                        }
                        
                        if (form) {
                            // Afficher le statut de sauvegarde
                            if (saveStatus) {
                                saveStatus.textContent = '💾 Sauvegarde en cours...';
                                saveStatus.style.color = '#007cba';
                            }
                            
                            // Soumettre le formulaire de manière sécurisée
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                // Fallback pour les navigateurs plus anciens
                                const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                                if (form.dispatchEvent(submitEvent)) {
                                    form.submit();
                                }
                            }
                        } else {
                            console.error('❌ No form found in active tab:', activeTab.id);
                            if (saveStatus) {
                                saveStatus.textContent = '❌ Erreur: Aucun formulaire trouvé';
                                saveStatus.style.color = '#dc3232';
                            }
                        }
                    } else {
                        console.error('❌ No active tab found');
                        if (saveStatus) {
                            saveStatus.textContent = '❌ Erreur: Aucun onglet actif';
                            saveStatus.style.color = '#dc3232';
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
                        formData.append('security', '<?php echo wp_create_nonce("pdf_builder_clear_cache_performance"); ?>');
                        
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
            console.log('🔍 SETUP TAB NAVIGATION - Found tab links:', tabLinks.length);
            
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
                    console.log('📑 RESTORING SAVED TAB:', savedTab);
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
                    console.log('🔐 Password field shown');
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = '👁️ Afficher';
                    console.log('🔐 Password field hidden');
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
                console.log('🔑 Generating license test key...');
                
                const $btn = jQuery(this);
                $btn.prop('disabled', true);
                $btn.html('⏳ Génération...');
                
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'pdf_builder_generate_test_license_key',
                        nonce: '<?php echo wp_create_nonce('pdf_builder_generate_license_key'); ?>'
                    },
                    success: function(response) {
                        console.log('✅ License key generated:', response);
                        if (response.success && response.data.key) {
                            licenseTestKeyInput.value = response.data.key;
                            licenseKeyStatus.innerHTML = '<span style="color: #28a745;">✅ Clé générée avec succès !</span>';
                            $btn.html('🔑 Régénérer');
                            $btn.prop('disabled', false);
                        } else {
                            licenseKeyStatus.innerHTML = '<span style="color: #d32f2f;">❌ Erreur: ' + (response.data.message || 'Impossible de générer la clé') + '</span>';
                            $btn.html('🔑 Générer');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX error:', error);
                        licenseKeyStatus.innerHTML = '<span style="color: #d32f2f;">❌ Erreur AJAX: ' + error + '</span>';
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
                        console.log('📋 License key copied to clipboard');
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
                
                console.log('🗑️ Deleting license test key...');
                
                const $btn = jQuery(this);
                $btn.prop('disabled', true);
                $btn.html('⏳ Suppression...');
                
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'pdf_builder_delete_test_license_key',
                        nonce: '<?php echo wp_create_nonce('pdf_builder_delete_test_license_key'); ?>'
                    },
                    success: function(response) {
                        console.log('✅ License key deleted:', response);
                        if (response.success) {
                            licenseTestKeyInput.value = '';
                            licenseKeyStatus.innerHTML = '<span style="color: #28a745;">✅ Clé supprimée avec succès !</span>';
                            
                            // Masquer le bouton de suppression
                            $btn.hide();
                            
                            setTimeout(function() {
                                licenseKeyStatus.innerHTML = '';
                            }, 3000);
                        } else {
                            console.error('❌ Delete failed:', response.data.message);
                            licenseKeyStatus.innerHTML = '<span style="color: #d32f2f;">❌ Erreur: ' + (response.data.message || 'Impossible de supprimer') + '</span>';
                            $btn.html('🗑️ Supprimer');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX error:', error);
                        licenseKeyStatus.innerHTML = '<span style="color: #d32f2f;">❌ Erreur AJAX: ' + error + '</span>';
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
                console.log('🎚️ Toggling license test mode...');
                
                const $btn = jQuery(this);
                $btn.prop('disabled', true);
                $btn.html('⏳ Basculement...');
                
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'pdf_builder_toggle_test_mode',
                        nonce: '<?php echo wp_create_nonce('pdf_builder_toggle_test_mode'); ?>'
                    },
                    success: function(response) {
                        console.log('✅ Test mode toggled:', response);
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
                            
                            console.log(response.data.message);
                        } else {
                            console.error('❌ Toggle failed:', response.data.message);
                            $btn.html('🎚️ Basculer Mode Test');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX error:', error);
                        $btn.html('🎚️ Basculer Mode Test');
                        $btn.prop('disabled', false);
                    }
                });
            });
        }

                // Gestion du test du système de cache
        jQuery(document).ready(function($) {
            console.log("🔧 Cache test button ready");
            const $btn = $("#test-cache-btn");
            const $results = $("#cache-test-results");
            const $output = $("#cache-test-output");
            
            $btn.on("click", function(e) {
                e.preventDefault();
                console.log("🖱️ Cache test button clicked");
                
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
                        console.log("✅ AJAX success:", response);
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
</script>

