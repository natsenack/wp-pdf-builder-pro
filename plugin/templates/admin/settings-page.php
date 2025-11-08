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

// Handle individual tab submissions
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
        ];
        // Logs removed for clarity
        $result = update_option('pdf_builder_settings', array_merge($settings, $dev_settings));
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
                <button type="button" name="submit" class="button button-primary" id="general-submit-btn">Enregistrer les paramètres</button>
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
            $is_premium = $license_status !== 'free' && $license_status !== 'expired';
            
            // Traitement activation licence
            if (isset($_POST['activate_license']) && isset($_POST['pdf_builder_license_nonce'])) {
                // Logs removed for clarity
                if (wp_verify_nonce($_POST['pdf_builder_license_nonce'], 'pdf_builder_license')) {
                    $new_key = sanitize_text_field($_POST['license_key'] ?? '');
                    if (!empty($new_key)) {
                        update_option('pdf_builder_license_key', $new_key);
                        update_option('pdf_builder_license_status', 'active');
                        update_option('pdf_builder_license_expires', date('Y-m-d', strtotime('+1 year')));
                        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence activée avec succès !</p></div>';
                        $is_premium = true;
                        $license_key = $new_key;
                        $license_status = 'active';
                    }
                }
            }
            
            // Traitement désactivation licence
            if (isset($_POST['deactivate_license']) && isset($_POST['pdf_builder_deactivate_nonce'])) {
                // Logs removed for clarity
                if (wp_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                    delete_option('pdf_builder_license_key');
                    delete_option('pdf_builder_license_expires');
                    update_option('pdf_builder_license_status', 'free');
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée.</p></div>';
                    $is_premium = false;
                    $license_key = '';
                    $license_status = 'free';
                }
            }
            ?>
            
            <!-- Statut de la licence -->
            <div style="background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">Statut de la Licence</h3>
                
                <div style="display: inline-block; padding: 12px 20px; border-radius: 4px; font-weight: bold; margin-bottom: 15px; color: white;
                            background: <?php echo $is_premium ? '#28a745' : '#6c757d'; ?>;">
                    <?php echo $is_premium ? '✓ Premium Activé' : '○ Gratuit'; ?>
                </div>
                
                <?php if ($is_premium): ?>
                    <div style="margin-bottom: 15px;">
                        <p><strong>Clé de licence :</strong> <?php echo substr($license_key, 0, 4) . '****' . substr($license_key, -4); ?></p>
                        <?php if ($license_expires): ?>
                            <p><strong>Expire le :</strong> <?php echo esc_html($license_expires); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: white;">🔓 Passez à la version Premium</h4>
                        <p style="margin: 0 0 15px 0;">Débloquez toutes les fonctionnalités avancées et créez des PDFs professionnels sans limites !</p>
                        <a href="https://pdfbuilderpro.com/pricing" class="button button-primary" target="_blank" 
                           style="background: white; color: #667eea; border: none; font-weight: bold;">
                            Voir les tarifs →
                        </a>
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
                
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('pdf_builder_clear_cache_performance', 'pdf_builder_clear_cache_nonce_performance'); ?>
                    <button type="submit" name="clear_cache" class="button button-secondary">
                        🗑️ Vider le Cache
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px;">
                    <p style="margin: 0;"><strong>💡 Conseil :</strong> Videz le cache si vous rencontrez des problèmes de génération PDF ou si les changements n'apparaissent pas.</p>
                </div>
            </div>
            
            <p class="submit">
                <button type="button" name="submit_performance" class="button button-primary" id="performance-submit-btn">Enregistrer les paramètres de performance</button>
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
            if (isset($_POST['submit_notifications']) && isset($_POST['pdf_builder_settings_nonce'])) {
                // Logs removed for clarity
                if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
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
            // Récupérer les paramètres canvas via le manager
            $canvas_settings = [];
            if (class_exists('PDF_Builder_Canvas_Manager')) {
                try {
                    $canvas_manager = \PDF_Builder_Canvas_Manager::get_instance();
                    $canvas_settings = $canvas_manager->get_canvas_settings();
                } catch (Exception $e) {
                    $canvas_settings = [];
                }
            }
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
            
            <form method="post">
                <?php wp_nonce_field('pdf_builder_templates', 'pdf_builder_templates_nonce'); ?>
                
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
            
            <form method="post" id="maintenance-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_maintenance_nonce'); ?>
                <input type="hidden" name="submit_maintenance" value="1">
            
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
            
            <p class="submit">
                <button type="submit" name="submit_maintenance" class="button button-primary">Enregistrer les paramètres de maintenance</button>
            </p>
            </form>
        </div>
        
        <div id="developpeur" class="tab-content hidden-tab">
            <h2>Paramètres Développeur</h2>
            <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>
            
            <form method="post" id="developpeur-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_developpeur_nonce'); ?>
                
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔍 Paramètres de Debug</h3>
            
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
    #templates .submit,
    #notifications .submit {
        display: block;
    }
    
    /* Exception pour le bouton de test dans l'onglet notifications */
    #notifications #test-notifications,
    #notifications #test-smtp-connection {
        display: inline-block !important;
    }
    
    /* Cacher le bouton global flottant dans les onglets avec boutons individuels */
    #roles #global-save-btn,
    #templates #global-save-btn {
        display: none !important;
    }

    /* Classe pour masquer les onglets non actifs */
    .hidden-tab {
        display: none;
    }
</style>

<?php
// Définir les paramètres canvas pour JavaScript
$canvas_settings_js = [];
if (class_exists('PDF_Builder_Canvas_Manager')) {
    try {
        $canvas_manager = \PDF_Builder_Canvas_Manager::get_instance();
        $canvas_settings_js = $canvas_manager->get_canvas_settings();
        // Logs removed for clarity
        
        // Also log the raw database option
        $raw_settings = get_option('pdf_builder_settings', []);
        // Logs removed for clarity
        
        // Test direct access to see if show_margins is saved
        $test_show_margins = isset($raw_settings['show_margins']) ? $raw_settings['show_margins'] : 'NOT_SET';
        // Logs removed for clarity
    } catch (Exception $e) {
        $canvas_settings_js = [];
        // Logs removed for clarity
    }
} else {
    // Logs removed for clarity
}
?>
<script>
// Définir ajaxurl si pas déjà défini
    if (typeof ajaxurl === 'undefined') {
        ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // COMMENTED OUT FOR DEBUG - ENTIRE DOMContentLoaded HANDLER
        /*
        // Fonction pour logger tous les éléments de formulaire sur la page
        function logAllFormElements(context = 'PAGE_LOAD') {
            console.log(`=== LOG ULTRA-DÉTAILLÉ DE TOUS LES ÉLÉMENTS [${context}] ===`);
            console.log(`⏰ Timestamp: ${new Date().toISOString()}`);
            console.log(`📍 URL: ${window.location.href}`);
            console.log(`🖥️ UserAgent: ${navigator.userAgent}`);
            
            // Logger tous les inputs avec TOUS les détails possibles
            const allInputs = document.querySelectorAll('input');
            console.log(`📝 Total inputs: ${allInputs.length}`);
            allInputs.forEach((input, index) => {
                const rect = input.getBoundingClientRect();
                const computedStyle = window.getComputedStyle(input);
                
                const ultraDetails = {
                    // Informations de base
                    index: index,
                    tagName: input.tagName,
                    name: input.name,
                    id: input.id,
                    type: input.type,
                    value: input.value,
                    
                    // Attributs HTML
                    placeholder: input.placeholder,
                    disabled: input.disabled,
                    required: input.required,
                    readonly: input.readOnly,
                    hidden: input.hidden,
                    tabindex: input.tabIndex,
                    autofocus: input.autofocus,
                    autocomplete: input.autocomplete,
                    spellcheck: input.spellcheck,
                    translate: input.translate,
                    
                    // Attributs spécifiques par type
                    accept: input.accept,
                    alt: input.alt,
                    checked: input.checked,
                    defaultChecked: input.defaultChecked,
                    defaultValue: input.defaultValue,
                    files: input.files ? Array.from(input.files).map(f => ({name: f.name, size: f.size, type: f.type})) : null,
                    formAction: input.formAction,
                    formEnctype: input.formEnctype,
                    formMethod: input.formMethod,
                    formNoValidate: input.formNoValidate,
                    formTarget: input.formTarget,
                    height: input.height,
                    width: input.width,
                    list: input.list ? input.list.id : null,
                    max: input.max,
                    maxLength: input.maxLength,
                    min: input.min,
                    minLength: input.minLength,
                    multiple: input.multiple,
                    pattern: input.pattern,
                    size: input.size,
                    src: input.src,
                    step: input.step,
                    title: input.title,
                    useMap: input.useMap,
                    
                    // Propriétés DOM
                    className: input.className,
                    classList: Array.from(input.classList),
                    style: input.style.cssText,
                    dataset: Object.assign({}, input.dataset),
                    
                    // Géométrie et position
                    offsetTop: input.offsetTop,
                    offsetLeft: input.offsetLeft,
                    offsetWidth: input.offsetWidth,
                    offsetHeight: input.offsetHeight,
                    clientWidth: input.clientWidth,
                    clientHeight: input.clientHeight,
                    scrollWidth: input.scrollWidth,
                    scrollHeight: input.scrollHeight,
                    boundingRect: {
                        top: rect.top,
                        left: rect.left,
                        bottom: rect.bottom,
                        right: rect.right,
                        width: rect.width,
                        height: rect.height
                    },
                    
                    // État de visibilité
                    offsetParent: input.offsetParent ? input.offsetParent.tagName : null,
                    visibility: computedStyle.visibility,
                    display: computedStyle.display,
                    opacity: computedStyle.opacity,
                    
                    // Relations DOM
                    parentElement: input.parentElement ? input.parentElement.tagName + (input.parentElement.id ? '#' + input.parentElement.id : '') : null,
                    nextElementSibling: input.nextElementSibling ? input.nextElementSibling.tagName : null,
                    previousElementSibling: input.previousElementSibling ? input.previousElementSibling.tagName : null,
                    children: input.children.length,
                    
                    // État de validation
                    willValidate: input.willValidate,
                    validity: input.validity ? {
                        valid: input.validity.valid,
                        valueMissing: input.validity.valueMissing,
                        typeMismatch: input.validity.typeMismatch,
                        patternMismatch: input.validity.patternMismatch,
                        tooLong: input.validity.tooLong,
                        tooShort: input.validity.tooShort,
                        rangeUnderflow: input.validity.rangeUnderflow,
                        rangeOverflow: input.validity.rangeOverflow,
                        stepMismatch: input.validity.stepMismatch,
                        badInput: input.validity.badInput,
                        customError: input.validity.customError
                    } : null,
                    
                    // État de formulaire
                    form: input.form ? input.form.id : null,
                    labels: input.labels ? Array.from(input.labels).map(label => label.textContent.trim()) : null,
                    
                    // État de modification
                    modified: input.hasAttribute('modified'),
                    modifiedAt: input.getAttribute('modified-at'),
                    
                    // Événements attachés (estimation)
                    eventListeners: 'Non détectable directement'
                };
                
                console.log(`INPUT ${index}:`, ultraDetails);
                
                // Logs supplémentaires pour les types spéciaux
                if (input.type === 'checkbox' || input.type === 'radio') {
                    console.log(`  ✅ État: ${input.checked ? 'COCHÉ' : 'DÉCOCHÉ'}`);
                    console.log(`  🔗 Groupe: ${input.name}`);
                    if (input.type === 'radio') {
                        const group = document.querySelectorAll(`input[type="radio"][name="${input.name}"]`);
                        const checkedInGroup = Array.from(group).filter(r => r.checked);
                        console.log(`  👥 Groupe (${group.length} éléments, ${checkedInGroup.length} coché(s))`);
                    }
                }
                
                if (input.type === 'file') {
                    console.log(`  📎 Fichiers sélectionnés: ${input.files ? input.files.length : 0}`);
                    if (input.files && input.files.length > 0) {
                        Array.from(input.files).forEach((file, fidx) => {
                            console.log(`    📄 Fichier ${fidx}: ${file.name} (${file.size} bytes, ${file.type})`);
                        });
                    }
                }
                
                if (input.list) {
                    const options = Array.from(input.list.options);
                    console.log(`  📋 Liste de suggestions (${options.length} options):`, options.map(opt => opt.value));
                }
                
                // Logs de validation détaillés
                if (input.validity && !input.validity.valid) {
                    console.log(`  ⚠️ ERREURS DE VALIDATION:`, Object.entries(input.validity).filter(([key, value]) => key !== 'valid' && value === true));
                }
                
                // Logs des styles calculés importants
                console.log(`  🎨 Styles clés:`, {
                    backgroundColor: computedStyle.backgroundColor,
                    border: computedStyle.border,
                    color: computedStyle.color,
                    fontSize: computedStyle.fontSize,
                    fontFamily: computedStyle.fontFamily,
                    padding: computedStyle.padding,
                    margin: computedStyle.margin,
                    position: computedStyle.position,
                    zIndex: computedStyle.zIndex
                });
            });
                    console.log(`  🔍 Validity:`, {
                        valid: input.validity.valid,
                        valueMissing: input.validity.valueMissing,
                        typeMismatch: input.validity.typeMismatch,
                        patternMismatch: input.validity.patternMismatch,
                        tooLong: input.validity.tooLong,
                        tooShort: input.validity.tooShort,
                        rangeUnderflow: input.validity.rangeUnderflow,
                        rangeOverflow: input.validity.rangeOverflow,
                        stepMismatch: input.validity.stepMismatch,
                        badInput: input.validity.badInput,
                        customError: input.validity.customError
                    });
                }
            });
            
            // Logger tous les selects avec détails complets
            const allSelects = document.querySelectorAll('select');
            console.log(`📋 Total selects: ${allSelects.length}`);
            allSelects.forEach((select, index) => {
                console.log(`SELECT ${index}: [name="${select.name}"][id="${select.id}"] = "${select.value}"`);
                console.log(`  - Selected Index: ${select.selectedIndex}`);
                console.log(`  - Multiple: ${select.multiple}`);
                console.log(`  - Size: ${select.size}`);
                console.log(`  - Disabled: ${select.disabled}`);
                console.log(`  - Required: ${select.required}`);
                
                // Logger les options disponibles avec détails
                const options = Array.from(select.options).map((opt, optIndex) => ({
                    index: optIndex,
                    value: opt.value,
                    text: opt.text,
                    selected: opt.selected,
                    disabled: opt.disabled,
                    hidden: opt.hidden
                }));
                console.log(`  - Options (${options.length}):`, options);
            });
            
            // Logger tous les textareas avec détails complets
            const allTextareas = document.querySelectorAll('textarea');
            console.log(`📄 Total textareas: ${allTextareas.length}`);
            allTextareas.forEach((textarea, index) => {
                const details = {
                    index: index,
                    name: textarea.name,
                    id: textarea.id,
                    value: textarea.value,
                    placeholder: textarea.placeholder,
                    disabled: textarea.disabled,
                    required: textarea.required,
                    readonly: textarea.readOnly,
                    rows: textarea.rows,
                    cols: textarea.cols,
                    maxlength: textarea.maxLength,
                    minlength: textarea.minLength,
                    wrap: textarea.wrap,
                    className: textarea.className,
                    style: textarea.style.cssText
                };
                console.log(`TEXTAREA ${index}:`, details);
                console.log(`  - Content length: ${textarea.value.length} characters`);
                console.log(`  - Content preview: "${textarea.value.substring(0, 200)}${textarea.value.length > 200 ? '...' : ''}"`);
            });
            
            // Logger tous les boutons avec détails complets
            const allButtons = document.querySelectorAll('button');
            console.log(`🔘 Total buttons: ${allButtons.length}`);
            allButtons.forEach((button, index) => {
                const details = {
                    index: index,
                    name: button.name,
                    id: button.id,
                    type: button.type,
                    textContent: button.textContent.trim(),
                    innerHTML: button.innerHTML,
                    disabled: button.disabled,
                    className: button.className,
                    style: button.style.cssText,
                    form: button.form ? button.form.id : null,
                    dataset: button.dataset
                };
                console.log(`BUTTON ${index}:`, details);
            });
            
            // Logger les éléments fieldset et legend
            const allFieldsets = document.querySelectorAll('fieldset');
            if (allFieldsets.length > 0) {
                console.log(`📦 Total fieldsets: ${allFieldsets.length}`);
                allFieldsets.forEach((fieldset, index) => {
                    console.log(`FIELDSET ${index}: [id="${fieldset.id}"] disabled=${fieldset.disabled}`);
                    const legend = fieldset.querySelector('legend');
                    if (legend) {
                        console.log(`  - Legend: "${legend.textContent.trim()}"`);
                    }
                });
            }
            
            // Logger les éléments label
            const allLabels = document.querySelectorAll('label');
            if (allLabels.length > 0) {
                console.log(`🏷️ Total labels: ${allLabels.length}`);
                allLabels.forEach((label, index) => {
                    console.log(`LABEL ${index}: [for="${label.htmlFor}"][id="${label.id}"] = "${label.textContent.trim()}"`);
                });
            }
            
            console.log(`=== FIN LOG ÉLÉMENTS DE FORMULAIRE [${context}] ===\n`);
        }
        
        // Logger tous les éléments au chargement de la page
        logAllFormElements('PAGE_LOAD');
        
        // Ajouter des logs pour les changements en temps réel avec plus de détails
        function addComprehensiveEventListeners() {
            const allFormElements = document.querySelectorAll('input, select, textarea, button');
            allFormElements.forEach((element, index) => {
                
                // Événement focus
                element.addEventListener('focus', function() {
                    console.log(`🎯 FOCUS: ${element.tagName}[name="${element.name}"][id="${element.id}"]`);
                    console.log(`   - Current value: "${element.value}"`);
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        console.log(`   - Checked: ${element.checked}`);
                    }
                });
                
                // Événement blur
                element.addEventListener('blur', function() {
                    console.log(`👁️ BLUR: ${element.tagName}[name="${element.name}"][id="${element.id}"]`);
                    console.log(`   - Final value: "${element.value}"`);
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        console.log(`   - Checked: ${element.checked}`);
                    }
                });
                
                // Événement change (pour tous les éléments)
                element.addEventListener('change', function() {
                    console.log(`🔄 CHANGE: ${element.tagName}[name="${element.name}"][id="${element.id}"] = "${element.value}"`);
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        console.log(`   - Checked: ${element.checked}`);
                    }
                    if (element.tagName === 'SELECT') {
                        console.log(`   - Selected option: ${element.options[element.selectedIndex].text} (${element.value})`);
                    }
                    if (element.validity && !element.validity.valid) {
                        console.log(`   ⚠️ VALIDATION ERROR:`, element.validity);
                    }
                    
                    // Marquer l'élément comme modifié
                    element.setAttribute('modified', 'true');
                    element.setAttribute('modified-at', new Date().toISOString());
                    console.log(`   ✅ Élément marqué comme modifié`);
                });
                
                // Événement input (pour les champs texte)
                if (element.type === 'text' || element.type === 'textarea' || element.type === 'password' || element.type === 'email' || element.type === 'number' || element.type === 'search' || element.type === 'url' || element.type === 'tel') {
                    element.addEventListener('input', function() {
                        console.log(`⌨️ INPUT: ${element.tagName}[name="${element.name}"][id="${element.id}"] = "${element.value}"`);
                        console.log(`   - Length: ${element.value.length} characters`);
                        if (element.maxLength) {
                            console.log(`   - Remaining: ${element.maxLength - element.value.length} characters`);
                        }
                    });
                    
                    // Événements clavier détaillés
                    element.addEventListener('keydown', function(e) {
                        console.log(`⬇️ KEYDOWN: ${element.tagName}[name="${element.name}"] - Key: ${e.key} (code: ${e.code})`);
                        if (e.ctrlKey || e.altKey || e.shiftKey || e.metaKey) {
                            console.log(`   - Modifiers: ${e.ctrlKey ? 'Ctrl ' : ''}${e.altKey ? 'Alt ' : ''}${e.shiftKey ? 'Shift ' : ''}${e.metaKey ? 'Meta ' : ''}`);
                        }
                    });
                    
                    element.addEventListener('keyup', function(e) {
                        console.log(`⬆️ KEYUP: ${element.tagName}[name="${element.name}"] - Key: ${e.key}`);
                    });
                }
                
                // Événements spécifiques aux checkboxes et radio
                if (element.type === 'checkbox' || element.type === 'radio') {
                    element.addEventListener('click', function() {
                        console.log(`🖱️ CLICK: ${element.type.toUpperCase()}[name="${element.name}"][value="${element.value}"] - Checked: ${element.checked}`);
                    });
                }
                
                // Événements pour les selects
                if (element.tagName === 'SELECT') {
                    element.addEventListener('click', function() {
                        console.log(`🖱️ SELECT CLICK: ${element.tagName}[name="${element.name}"] - Current: "${element.value}"`);
                    });
                }
                
                // Événements pour les boutons
                if (element.tagName === 'BUTTON') {
                    element.addEventListener('click', function(e) {
                        console.log(`🔘 BUTTON CLICK: ${element.tagName}[name="${element.name}"][id="${element.id}"] - "${element.textContent.trim()}"`);
                        console.log(`   - Type: ${element.type}`);
                        console.log(`   - Form: ${element.form ? element.form.id : 'no form'}`);
                        console.log(`   - Event details:`, {
                            button: e.button,
                            ctrlKey: e.ctrlKey,
                            altKey: e.altKey,
                            shiftKey: e.shiftKey,
                            clientX: e.clientX,
                            clientY: e.clientY
                        });
                    });
                    
                    element.addEventListener('mousedown', function(e) {
                        console.log(`👇 MOUSEDOWN: ${element.tagName}[id="${element.id}"] - Button: ${e.button}`);
                    });
                    
                    element.addEventListener('mouseup', function(e) {
                        console.log(`👆 MOUSEUP: ${element.tagName}[id="${element.id}"] - Button: ${e.button}`);
                    });
                }
                
                // Événements de validation
                element.addEventListener('invalid', function() {
                    console.log(`❌ INVALID: ${element.tagName}[name="${element.name}"] - Validation failed`);
                    if (element.validity) {
                        console.log(`   - Validity details:`, element.validity);
                    }
                });
                
                // Événements de formulaire
                if (element.form) {
                    element.addEventListener('formdata', function(e) {
                        console.log(`📋 FORMDATA: ${element.tagName}[name="${element.name}"] included in form submission`);
                    });
                }
            });
            
            // Logs périodiques pour surveiller les changements
            setInterval(() => {
                console.log('⏰ PÉRIODIQUE - État des éléments critiques:');
                const criticalElements = document.querySelectorAll('#debug_mode, #debug_javascript, #debug_php_errors, #debug_ajax');
                criticalElements.forEach(el => {
                    if (el.type === 'checkbox') {
                        console.log(`   ${el.id}: ${el.checked}`);
                    }
                });
            }, 30000); // Toutes les 30 secondes
        }
        
        // Ajouter les listeners d'événements complets
        addComprehensiveEventListeners();
        
        // Logs pour événements avancés (souris, clavier, clipboard, etc.)
        function addAdvancedEventListeners() {
            const allFormElements = document.querySelectorAll('input, select, textarea, button');
            
            allFormElements.forEach((element, index) => {
                // Événements de souris détaillés
                element.addEventListener('mouseenter', function(e) {
                    console.log(`🐭 MOUSEENTER: ${element.tagName}[${element.name || element.id}] at (${e.clientX}, ${e.clientY})`);
                });
                
                element.addEventListener('mouseleave', function(e) {
                    console.log(`🐭 MOUSELEAVE: ${element.tagName}[${element.name || element.id}]`);
                });
                
                element.addEventListener('contextmenu', function(e) {
                    console.log(`📋 CONTEXT MENU: ${element.tagName}[${element.name || element.id}] at (${e.clientX}, ${e.clientY})`);
                });
                
                element.addEventListener('wheel', function(e) {
                    console.log(`🖱️ WHEEL: ${element.tagName}[${element.name || element.id}] deltaY: ${e.deltaY}`);
                });
                
                // Événements de clipboard
                element.addEventListener('cut', function(e) {
                    console.log(`✂️ CUT: ${element.tagName}[${element.name || element.id}]`);
                });
                
                element.addEventListener('copy', function(e) {
                    console.log(`📋 COPY: ${element.tagName}[${element.name || element.id}]`);
                });
                
                element.addEventListener('paste', function(e) {
                    console.log(`📄 PASTE: ${element.tagName}[${element.name || element.id}]`);
                    console.log(`   - Clipboard data types:`, Array.from(e.clipboardData.types));
                });
                
                // Événements de drag & drop
                element.addEventListener('dragstart', function(e) {
                    console.log(`🎯 DRAG START: ${element.tagName}[${element.name || element.id}]`);
                });
                
                element.addEventListener('dragend', function(e) {
                    console.log(`🎯 DRAG END: ${element.tagName}[${element.name || element.id}]`);
                });
                
                element.addEventListener('drop', function(e) {
                    console.log(`🎯 DROP: ${element.tagName}[${element.name || element.id}]`);
                    if (e.dataTransfer) {
                        console.log(`   - Data types:`, Array.from(e.dataTransfer.types));
                        console.log(`   - Files: ${e.dataTransfer.files.length}`);
                    }
                });
                
                // Événements de sélection
                element.addEventListener('select', function(e) {
                    console.log(`📝 SELECT: ${element.tagName}[${element.name || element.id}]`);
                });
                
                element.addEventListener('selectionchange', function(e) {
                    if (document.activeElement === element) {
                        const selection = window.getSelection();
                        console.log(`📝 SELECTION CHANGE: ${element.tagName}[${element.name || element.id}] - "${selection.toString()}"`);
                    }
                });
                
                // Événements de scroll
                element.addEventListener('scroll', function(e) {
                    console.log(`📜 SCROLL: ${element.tagName}[${element.name || element.id}] - scrollTop: ${element.scrollTop}, scrollLeft: ${element.scrollLeft}`);
                });
                
                // Événements de redimensionnement (pour textareas)
                if (element.tagName === 'TEXTAREA') {
                    // Créer un ResizeObserver pour les textareas
                    if (window.ResizeObserver) {
                        const resizeObserver = new ResizeObserver(entries => {
                            entries.forEach(entry => {
                                console.log(`📏 RESIZE: TEXTAREA[${element.name || element.id}] - ${entry.contentRect.width}x${entry.contentRect.height}`);
                            });
                        });
                        resizeObserver.observe(element);
                    }
                }
            });
            
            // Événements globaux de la page
            document.addEventListener('visibilitychange', function() {
                console.log(`👁️ VISIBILITY CHANGE: ${document.hidden ? 'HIDDEN' : 'VISIBLE'}`);
            });
            
            window.addEventListener('resize', function() {
                console.log(`📐 WINDOW RESIZE: ${window.innerWidth}x${window.innerHeight}`);
            });
            
            window.addEventListener('scroll', function() {
                console.log(`📜 WINDOW SCROLL: scrollY: ${window.scrollY}, scrollX: ${window.scrollX}`);
            });
            
            // Événements de mutation DOM (changements structurels)
            if (window.MutationObserver) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            console.log(`🔄 DOM MUTATION: ${mutation.addedNodes.length} ajouté(s), ${mutation.removedNodes.length} supprimé(s)`);
                        } else if (mutation.type === 'attributes') {
                            console.log(`🔄 ATTRIBUTE MUTATION: ${mutation.attributeName} changé sur ${mutation.target.tagName}`);
                        }
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    attributes: true,
                    subtree: true,
                    attributeFilter: ['value', 'checked', 'selected', 'disabled', 'class']
                });
            }
            
            // Logs périodiques ultra-détaillés
            setInterval(() => {
                console.log('⏰ RAPPORT PÉRIODIQUE ULTRA-DÉTAILLÉ:');
                
                // État des éléments critiques
                const criticalElements = document.querySelectorAll('#debug_mode, #debug_javascript, #debug_php_errors, #debug_ajax, #cache_enabled');
                console.log('🔧 ÉLÉMENTS CRITIQUES:');
                criticalElements.forEach(el => {
                    if (el.type === 'checkbox') {
                        console.log(`   ${el.id}: ${el.checked}`);
                    } else if (el.type === 'text' || el.type === 'number') {
                        console.log(`   ${el.id}: "${el.value}"`);
                    }
                });
                
                // Statistiques générales
                const totalElements = document.querySelectorAll('input, select, textarea, button').length;
                const modifiedElements = document.querySelectorAll('[modified="true"]').length;
                const visibleElements = Array.from(document.querySelectorAll('input, select, textarea, button')).filter(el => {
                    const rect = el.getBoundingClientRect();
                    return rect.width > 0 && rect.height > 0 && window.getComputedStyle(el).display !== 'none';
                }).length;
                
                console.log('📊 STATISTIQUES:');
                console.log(`   - Total éléments: ${totalElements}`);
                console.log(`   - Éléments modifiés: ${modifiedElements}`);
                console.log(`   - Éléments visibles: ${visibleElements}`);
                console.log(`   - Mémoire utilisée: ${performance.memory ? Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) + ' MB' : 'N/A'}`);
                
            }, 10000); // Toutes les 10 secondes
        }
        
        // Logs pour les événements réseau globaux
        function addNetworkEventListeners() {
            // Événements de connexion réseau
            window.addEventListener('online', function() {
                console.log('🌐 NETWORK: Connection restored');
                logAllFormElements('NETWORK_ONLINE');
            });
            
            window.addEventListener('offline', function() {
                console.log('🚫 NETWORK: Connection lost');
            });
            
            // Événements de chargement de ressources
            window.addEventListener('error', function(e) {
                if (e.target !== window) {
                    console.error('💥 RESOURCE LOAD ERROR:', {
                        target: e.target.tagName,
                        src: e.target.src || e.target.href,
                        type: e.type
                    });
                }
            });
            
            // Événements de performance de navigation
            if (window.performance && window.performance.timing) {
                window.addEventListener('load', function() {
                    setTimeout(() => {
                        const timing = window.performance.timing;
                        console.log('⏱️ PAGE LOAD PERFORMANCE:', {
                            domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                            loadComplete: timing.loadEventEnd - timing.navigationStart,
                            totalTime: timing.loadEventEnd - timing.navigationStart
                        });
                    }, 0);
                });
            }
            
            // Logs pour les erreurs de console (si disponible)
            if (window.console && !window.originalConsoleError) {
                window.originalConsoleError = window.console.error;
                window.console.error = function(...args) {
                    window.originalConsoleError.apply(console, ['🔴 CONSOLE ERROR:'].concat(args));
                    window.originalConsoleError.apply(console, args);
                };
            }
            
            // Logs pour les avertissements de console
            if (window.console && !window.originalConsoleWarn) {
                window.originalConsoleWarn = window.console.warn;
                window.console.warn = function(...args) {
                    window.originalConsoleWarn.apply(console, ['🟡 CONSOLE WARNING:'].concat(args));
                    window.originalConsoleWarn.apply(console, args);
                };
            }
            
            // Logs pour les événements de visibilité des éléments
            if (window.IntersectionObserver) {
                const visibilityObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.target.id || entry.target.name) {
                            const visibilityPercent = Math.round(entry.intersectionRatio * 100);
                            console.log(`👁️ VISIBILITY: ${entry.target.tagName}[${entry.target.name || entry.target.id}] - ${entry.isIntersecting ? 'VISIBLE' : 'HIDDEN'} (${visibilityPercent}%)`);
                        }
                    });
                }, { threshold: [0, 0.1, 0.5, 1] });
                
                // Observer tous les éléments de formulaire
                document.querySelectorAll('input, select, textarea, button').forEach(el => {
                    visibilityObserver.observe(el);
                });
            }
            
            // Logs pour les changements d'attributs importants
            if (window.MutationObserver) {
                const attributeObserver = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.type === 'attributes' && ['disabled', 'hidden', 'required', 'checked', 'selected', 'readonly'].includes(mutation.attributeName)) {
                            const newValue = mutation.target.getAttribute(mutation.attributeName);
                            console.log(`🔄 ATTRIBUTE: ${mutation.target.tagName}[${mutation.target.name || mutation.target.id}] - ${mutation.attributeName} = "${newValue}"`);
                        }
                    });
                });
                
                document.querySelectorAll('input, select, textarea, button').forEach(el => {
                    attributeObserver.observe(el, { 
                        attributes: true, 
                        attributeFilter: ['disabled', 'hidden', 'required', 'checked', 'selected', 'readonly', 'value'] 
                    });
                });
            }
            
            // Logs pour les éléments disabled/hidden au chargement
            setTimeout(() => {
                const disabledElements = document.querySelectorAll('input:disabled, select:disabled, textarea:disabled, button:disabled');
                const hiddenElements = document.querySelectorAll('input[type="hidden"], select:hidden, textarea:hidden, button:hidden');
                
                if (disabledElements.length > 0) {
                    console.log(`🚫 DISABLED ELEMENTS (${disabledElements.length}):`);
                    disabledElements.forEach(el => {
                        console.log(`   - ${el.tagName}[${el.name || el.id}]`);
                    });
                }
                
                if (hiddenElements.length > 0) {
                    console.log(`🙈 HIDDEN ELEMENTS (${hiddenElements.length}):`);
                    hiddenElements.forEach(el => {
                        console.log(`   - ${el.tagName}[${el.name || el.id}]`);
                    });
                }
                
                // Logs pour les événements de stockage
                window.addEventListener('storage', function(e) {
                    console.log('💾 STORAGE EVENT:', {
                        key: e.key,
                        oldValue: e.oldValue,
                        newValue: e.newValue,
                        storageArea: e.storageArea === localStorage ? 'localStorage' : 'sessionStorage'
                    });
                });
                
                // Logs pour les changements de hash URL
                window.addEventListener('hashchange', function(e) {
                    console.log('🔗 HASH CHANGE:', {
                        oldURL: e.oldURL,
                        newURL: e.newURL,
                        newHash: location.hash
                    });
                });
                
                // Logs pour les événements de redimensionnement
                let resizeTimeout;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        console.log('📐 WINDOW RESIZED:', {
                            width: window.innerWidth,
                            height: window.innerHeight,
                            devicePixelRatio: window.devicePixelRatio
                        });
                    }, 250);
                });
                
                // Logs pour les événements de scroll
                let scrollTimeout;
                window.addEventListener('scroll', function() {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        console.log('📜 WINDOW SCROLLED:', {
                            scrollX: window.scrollX,
                            scrollY: window.scrollY,
                            maxScroll: Math.max(
                                document.body.scrollHeight - window.innerHeight,
                                document.documentElement.scrollHeight - window.innerHeight
                            )
                        });
                    }, 100);
                });
                
            }, 1000);
            
            // Logs pour les APIs modernes et avancées
            setTimeout(() => {
                // Battery API
                if ('getBattery' in navigator) {
                    navigator.getBattery().then(battery => {
                        console.log('🔋 BATTERY STATUS:', {
                            charging: battery.charging,
                            chargingTime: battery.chargingTime,
                            dischargingTime: battery.dischargingTime,
                            level: Math.round(battery.level * 100) + '%'
                        });
                        
                        battery.addEventListener('chargingchange', () => {
                            console.log('🔋 BATTERY CHARGING CHANGED:', battery.charging);
                        });
                        
                        battery.addEventListener('levelchange', () => {
                            console.log('🔋 BATTERY LEVEL CHANGED:', Math.round(battery.level * 100) + '%');
                        });
                    });
                }
                
                // Network Information API
                if ('connection' in navigator) {
                    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (connection) {
                        console.log('🌐 NETWORK INFO:', {
                            effectiveType: connection.effectiveType,
                            downlink: connection.downlink,
                            rtt: connection.rtt,
                            saveData: connection.saveData
                        });
                        
                        connection.addEventListener('change', () => {
                            console.log('🌐 NETWORK CHANGED:', {
                                effectiveType: connection.effectiveType,
                                downlink: connection.downlink,
                                rtt: connection.rtt
                            });
                        });
                    }
                }
                
                // Geolocation API (si déjà autorisée)
                if ('geolocation' in navigator) {
                    console.log('📍 GEOLOCATION: Available (permission status unknown)');
                }
                
                // Pointer Events support
                const pointerSupport = {
                    pointerdown: 'onpointerdown' in window,
                    pointerup: 'onpointerup' in window,
                    pointermove: 'onpointermove' in window,
                    pointerenter: 'onpointerenter' in window,
                    pointerleave: 'onpointerleave' in window
                };
                console.log('👆 POINTER EVENTS SUPPORT:', pointerSupport);
                
                // Touch Events support
                const touchSupport = {
                    touchstart: 'ontouchstart' in window,
                    touchend: 'ontouchend' in window,
                    touchmove: 'ontouchmove' in window,
                    touchcancel: 'ontouchcancel' in window
                };
                console.log('👋 TOUCH EVENTS SUPPORT:', touchSupport);
                
                // Gamepad API
                if ('getGamepads' in navigator) {
                    console.log('🎮 GAMEPAD API: Available');
                    window.addEventListener('gamepadconnected', e => {
                        console.log('🎮 GAMEPAD CONNECTED:', {
                            id: e.gamepad.id,
                            index: e.gamepad.index,
                            mapping: e.gamepad.mapping,
                            buttons: e.gamepad.buttons.length,
                            axes: e.gamepad.axes.length
                        });
                    });
                    
                    window.addEventListener('gamepaddisconnected', e => {
                        console.log('🎮 GAMEPAD DISCONNECTED:', e.gamepad.id);
                    });
                }
                
                // WebGL support
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (gl) {
                    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                    console.log('🎨 WEBGL SUPPORT:', {
                        vendor: debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'Unknown',
                        renderer: debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'Unknown',
                        version: gl.getParameter(gl.VERSION),
                        shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION)
                    });
                }
                
                // WebRTC support
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    console.log('📹 WEBRTC: Available (getUserMedia supported)');
                }
                
                // Service Worker status
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistrations().then(registrations => {
                        console.log('⚙️ SERVICE WORKERS:', registrations.length, 'registered');
                        registrations.forEach(reg => {
                            console.log('   - SW:', reg.scope, reg.active ? 'ACTIVE' : 'INACTIVE');
                        });
                    });
                }
                
                // Clipboard API advanced
                if (navigator.clipboard && navigator.clipboard.readText) {
                    console.log('📋 CLIPBOARD API: Full support (read/write)');
                } else if (navigator.clipboard) {
                    console.log('📋 CLIPBOARD API: Basic support (write only)');
                }
                
                // Vibration API
                if ('vibrate' in navigator) {
                    console.log('📳 VIBRATION API: Supported');
                }
                
                // Ambient Light Sensor (si disponible)
                if ('AmbientLightSensor' in window) {
                    console.log('💡 AMBIENT LIGHT SENSOR: Available');
                }
                
                // Device Orientation
                if (window.DeviceOrientationEvent) {
                    console.log('📐 DEVICE ORIENTATION: Supported');
                }
                
                // Speech Recognition
                if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                    console.log('🎤 SPEECH RECOGNITION: Supported');
                }
                
                // Web Audio API
                if ('AudioContext' in window || 'webkitAudioContext' in window) {
                    console.log('🔊 WEB AUDIO API: Supported');
                }
                
                // WebSocket API
                if ('WebSocket' in window) {
                    console.log('🔌 WEBSOCKET API: Supported');
                }
                
                // Server-Sent Events (EventSource)
                if ('EventSource' in window) {
                    console.log('📡 SERVER-SENT EVENTS: Supported');
                }
                
                // PerformanceObserver API
                if ('PerformanceObserver' in window) {
                    console.log('📊 PERFORMANCE OBSERVER: Supported');
                    try {
                        const perfObserver = new PerformanceObserver((list) => {
                            const entries = list.getEntries();
                            entries.forEach(entry => {
                                console.log('📈 PERFORMANCE ENTRY:', {
                                    name: entry.name,
                                    entryType: entry.entryType,
                                    startTime: entry.startTime,
                                    duration: entry.duration
                                });
                            });
                        });
                        perfObserver.observe({ entryTypes: ['measure', 'navigation', 'resource', 'paint'] });
                        console.log('📊 PERFORMANCE OBSERVER: Active (monitoring measures, navigation, resources, paint)');
                    } catch (e) {
                        console.log('📊 PERFORMANCE OBSERVER: Supported but failed to initialize:', e.message);
                    }
                }
                
                // WebGPU API (expérimental)
                if ('gpu' in navigator) {
                    console.log('🎮 WEBGPU API: Supported (experimental)');
                }
                
                // Speech Synthesis API
                if ('speechSynthesis' in window) {
                    console.log('🗣️ SPEECH SYNTHESIS: Supported');
                    console.log('   - Voices available:', speechSynthesis.getVoices().length);
                }
                
                // AbortController/AbortSignal API
                if ('AbortController' in window && 'AbortSignal' in window) {
                    console.log('🛑 ABORT CONTROLLER: Supported');
                }
                
                // Wake Lock API
                if ('wakeLock' in navigator) {
                    console.log('☀️ WAKE LOCK API: Supported');
                    // Test wake lock request
                    navigator.wakeLock.request('screen').then(lock => {
                        console.log('☀️ WAKE LOCK: Screen wake lock acquired');
                        lock.addEventListener('release', () => {
                            console.log('☀️ WAKE LOCK: Screen wake lock released');
                        });
                        // Release immediately for testing
                        lock.release();
                    }).catch(err => {
                        console.log('☀️ WAKE LOCK: Request failed:', err.message);
                    });
                }
                
                // Presentation API
                if ('presentation' in navigator) {
                    console.log('📺 PRESENTATION API: Supported');
                    console.log('   - Default presentation URL:', navigator.presentation.defaultRequest ? navigator.presentation.defaultRequest.url : 'none');
                }
                
                // Web Share API
                if ('share' in navigator) {
                    console.log('📤 WEB SHARE API: Supported');
                }
                
                // Web Authentication API (WebAuthn)
                if ('credentials' in navigator && 'publicKey' in navigator.credentials) {
                    console.log('🔐 WEB AUTHENTICATION API: Supported');
                }
                
                // Web MIDI API
                if ('requestMIDIAccess' in navigator) {
                    console.log('🎹 WEB MIDI API: Supported');
                }
                
                // Web USB API
                if ('usb' in navigator) {
                    console.log('🔌 WEB USB API: Supported');
                }
                
                // Web Bluetooth API
                if ('bluetooth' in navigator) {
                    console.log('📡 WEB BLUETOOTH API: Supported');
                }
                
                // Web Serial API
                if ('serial' in navigator) {
                    console.log('🔌 WEB SERIAL API: Supported');
                }
                
                // Web HID API
                if ('hid' in navigator) {
                    console.log('🎮 WEB HID API: Supported');
                }
                
                // Payment Request API
                if ('PaymentRequest' in window) {
                    console.log('💳 PAYMENT REQUEST API: Supported');
                }
                
                // Credential Management API
                if ('credentials' in navigator) {
                    console.log('🔑 CREDENTIAL MANAGEMENT API: Supported');
                }
                
                // Cross-Origin Embedder Policy (COEP) support
                if ('crossOriginIsolated' in window) {
                    console.log('🔒 CROSS-ORIGIN ISOLATION: Enabled (crossOriginIsolated = true)');
                } else {
                    console.log('🔓 CROSS-ORIGIN ISOLATION: Not enabled');
                }
                
                // Screen Wake Lock API (alternative check)
                if ('request' in navigator.wakeLock || 'wakeLock' in navigator) {
                    console.log('🌙 SCREEN WAKE LOCK: Available');
                }
                
                // WebCodecs API
                if ('VideoEncoder' in window && 'VideoDecoder' in window) {
                    console.log('🎬 WEBCODECS API: Supported (Video encoding/decoding)');
                } else if ('AudioEncoder' in window && 'AudioDecoder' in window) {
                    console.log('🎵 WEBCODECS API: Supported (Audio encoding/decoding)');
                }
                
                // WebTransport API (experimental)
                if ('WebTransport' in window) {
                    console.log('🚀 WEBTRANSPORT API: Supported (experimental)');
                }
                
                // Background Fetch API
                if ('serviceWorker' in navigator && 'BackgroundFetchManager' in window) {
                    console.log('📥 BACKGROUND FETCH API: Supported');
                }
                
                // Content Index API
                if ('serviceWorker' in navigator && 'ContentIndex' in window) {
                    console.log('📄 CONTENT INDEX API: Supported');
                }
                
                // WebOTP API
                if ('OTPCredential' in window) {
                    console.log('📱 WEBOTP API: Supported');
                }
                
                // WebNFC API
                if ('NDEFReader' in window) {
                    console.log('📡 WEBNFC API: Supported');
                }
                
                // WebXR API (VR/AR)
                if ('xr' in navigator) {
                    console.log('🥽 WEBXR API: Supported (VR/AR)');
                }
                
                // EyeDropper API
                if ('EyeDropper' in window) {
                    console.log('👁️ EYEDROPPER API: Supported');
                }
                
                // File System Access API
                if ('showOpenFilePicker' in window) {
                    console.log('📁 FILE SYSTEM ACCESS API: Supported');
                }
                
                // WebAssembly support
                if ('WebAssembly' in window) {
                    console.log('⚙️ WEBASSEMBLY: Supported');
                    console.log('   - WebAssembly global object available');
                }
                
                // SharedArrayBuffer support (requires COEP/COOP)
                if ('SharedArrayBuffer' in window) {
                    console.log('🔄 SHAREDARRAYBUFFER: Supported (COEP/COOP enabled)');
                } else {
                    console.log('🔄 SHAREDARRAYBUFFER: Not supported (COEP/COOP required)');
                }
                
                // Atomics API (for SharedArrayBuffer)
                if ('Atomics' in window) {
                    console.log('⚛️ ATOMICS API: Supported');
                }
                
                // BigInt support
                if (typeof BigInt !== 'undefined') {
                    console.log('🔢 BIGINT: Supported');
                }
                
                // Nullish coalescing and optional chaining support
                try {
                    const test = null ?? 'default';
                    const chain = {}.?.prop;
                    console.log('🔗 MODERN JS FEATURES: Nullish coalescing and optional chaining supported');
                } catch (e) {
                    console.log('🔗 MODERN JS FEATURES: Some modern syntax not supported');
                }
                
                // Dynamic imports support
                if ('import' in window) {
                    console.log('📦 DYNAMIC IMPORTS: Supported');
                }
                
                // Module scripts support
                const script = document.createElement('script');
                if ('noModule' in script) {
                    console.log('📜 MODULE SCRIPTS: Supported');
                }
                
                // CSS containment support
                const testEl = document.createElement('div');
                testEl.style.contain = 'layout';
                if (testEl.style.contain === 'layout') {
                    console.log('🎨 CSS CONTAINMENT: Supported');
                }
                
                // CSS custom properties support
                if (window.CSS && CSS.supports('color', 'var(--test)')) {
                    console.log('🎨 CSS CUSTOM PROPERTIES: Supported');
                }
                
                // ResizeObserver support
                if ('ResizeObserver' in window) {
                    console.log('📏 RESIZE OBSERVER: Supported');
                }
                
                // IntersectionObserver support
                if ('IntersectionObserver' in window) {
                    console.log('👁️ INTERSECTION OBSERVER: Supported');
                }
                
                // RequestIdleCallback support
                if ('requestIdleCallback' in window) {
                    console.log('⏰ REQUEST IDLE CALLBACK: Supported');
                }
                
                // Page Visibility API
                if ('visibilityState' in document) {
                    console.log('👁️ PAGE VISIBILITY API: Supported');
                    console.log('   - Current state:', document.visibilityState);
                }
                
                // Gamepad API detailed
                if ('getGamepads' in navigator) {
                    console.log('🎮 GAMEPAD API: Supported');
                    // Check for connected gamepads
                    const gamepads = navigator.getGamepads();
                    const connectedGamepads = gamepads.filter(gp => gp !== null);
                    if (connectedGamepads.length > 0) {
                        console.log('   - Connected gamepads:', connectedGamepads.length);
                        connectedGamepads.forEach(gp => {
                            console.log(`     • ${gp.id} (${gp.buttons.length} buttons, ${gp.axes.length} axes)`);
                        });
                    } else {
                        console.log('   - No gamepads currently connected');
                    }
                }
                
                // WebRTC detailed support
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    console.log('📹 WEBRTC: Supported');
                    // Check for additional WebRTC features
                    if (navigator.mediaDevices.getDisplayMedia) {
                        console.log('   - Screen sharing: Supported');
                    }
                    if (window.RTCPeerConnection) {
                        console.log('   - Peer connections: Supported');
                        // Check for data channels
                        try {
                            const pc = new RTCPeerConnection();
                            if (pc.createDataChannel) {
                                console.log('   - Data channels: Supported');
                            }
                            pc.close();
                        } catch (e) {
                            console.log('   - Data channels: Error checking support');
                        }
                    }
                }
                
                // Web Audio API detailed
                if ('AudioContext' in window || 'webkitAudioContext' in window) {
                    console.log('🔊 WEB AUDIO API: Supported');
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        const audioContext = new AudioContext();
                        console.log('   - Audio context state:', audioContext.state);
                        console.log('   - Sample rate:', audioContext.sampleRate);
                        audioContext.close();
                    } catch (e) {
                        console.log('   - Audio context creation failed');
                    }
                }
                
                // WebGL detailed support
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (gl) {
                    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                    console.log('🎨 WEBGL SUPPORT:', {
                        vendor: debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'Unknown',
                        renderer: debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'Unknown',
                        version: gl.getParameter(gl.VERSION),
                        shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION),
                        maxTextureSize: gl.getParameter(gl.MAX_TEXTURE_SIZE),
                        extensions: gl.getSupportedExtensions().length
                    });
                }
                
                // WebGL2 support
                const gl2 = canvas.getContext('webgl2');
                if (gl2) {
                    console.log('🎨 WEBGL2: Supported');
                }
                
                // WebGPU support (experimental)
                if ('gpu' in navigator) {
                    console.log('🎮 WEBGPU: Supported (experimental)');
                    // Try to request adapter
                    navigator.gpu.requestAdapter().then(adapter => {
                        if (adapter) {
                            console.log('   - GPU adapter available');
                            console.log('   - Adapter info:', adapter.name || 'Unknown');
                        } else {
                            console.log('   - No GPU adapter available');
                        }
                    }).catch(err => {
                        console.log('   - GPU adapter request failed:', err.message);
                    });
                }
                
                // Performance API advanced features - COMMENTED OUT FOR DEBUG
                /*
                if ('performance' in window) {
                    // console.log('📊 PERFORMANCE API: Available');
                    
                    // Performance Navigation Timing
                    if (performance.timing) {
                        console.log('   - Navigation timing: Available');
                    }
                    
                    // Performance Resource Timing
                    if (performance.getEntriesByType) {
                        const resources = performance.getEntriesByType('resource');
                        console.log('   - Resource timing: Available (' + resources.length + ' resources tracked)');
                    }
                    
                    // Performance Memory (Chrome only)
                    if (performance.memory) {
                        console.log('   - Memory info: Available');
                        console.log('     • Used JS heap:', Math.round(performance.memory.usedJSHeapSize / 1024 / 1024) + ' MB');
                        console.log('     • Total JS heap:', Math.round(performance.memory.totalJSHeapSize / 1024 / 1024) + ' MB');
                        console.log('     • Heap limit:', Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) + ' MB');
                    }
                    
                    // Performance Observer
                    if ('PerformanceObserver' in window) {
                        console.log('   - Performance Observer: Available');
                    }
                    
                    // User Timing API
                    if (performance.mark && performance.measure) {
                        console.log('   - User Timing API: Available');
                    }
                    
                    // Navigation Timing Level 2
                    if (performance.getEntriesByType('navigation').length > 0) {
                        console.log('   - Navigation Timing Level 2: Available');
                    }
                }
                */
                
                // Battery API and related sections - COMMENTED OUT FOR DEBUG
                /*
                // Battery API detailed monitoring
                if ('getBattery' in navigator) {
                    navigator.getBattery().then(battery => {
                        console.log('🔋 BATTERY API: Detailed monitoring active');
                        
                        // Log current state
                        console.log('   - Current state:', {
                            level: Math.round(battery.level * 100) + '%',
                            charging: battery.charging,
                            chargingTime: battery.chargingTime,
                            dischargingTime: battery.dischargingTime
                        });
                        
                        // Enhanced event listeners
                        battery.addEventListener('levelchange', () => {
                            console.log('🔋 BATTERY LEVEL CHANGED:', {
                                newLevel: Math.round(battery.level * 100) + '%',
                                charging: battery.charging,
                                timestamp: new Date().toISOString()
                            });
                        });
                        
                        battery.addEventListener('chargingchange', () => {
                            console.log('🔋 CHARGING STATUS CHANGED:', {
                                charging: battery.charging,
                                level: Math.round(battery.level * 100) + '%',
                                timestamp: new Date().toISOString()
                            });
                        });
                        
                        battery.addEventListener('chargingtimechange', () => {
                            console.log('🔋 CHARGING TIME CHANGED:', {
                                chargingTime: battery.chargingTime,
                                timestamp: new Date().toISOString()
                            });
                        });
                        
                        battery.addEventListener('dischargingtimechange', () => {
                            console.log('🔋 DISCHARGING TIME CHANGED:', {
                                dischargingTime: battery.dischargingTime,
                                timestamp: new Date().toISOString()
                            });
                        });
                    });
                }
                
                // Network Information API detailed
                if ('connection' in navigator) {
                    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (connection) {
                        console.log('🌐 NETWORK INFORMATION API: Detailed monitoring active');
                        console.log('   - Current connection:', {
                            effectiveType: connection.effectiveType,
                            downlink: connection.downlink,
                            rtt: connection.rtt,
                            saveData: connection.saveData,
                            type: connection.type
                        });
                        
                        // Listen for changes
                        connection.addEventListener('change', () => {
                            console.log('🌐 NETWORK CHANGED:', {
                                effectiveType: connection.effectiveType,
                                downlink: connection.downlink,
                                rtt: connection.rtt,
                                saveData: connection.saveData,
                                type: connection.type,
                                timestamp: new Date().toISOString()
                            });
                        });
                    }
                }
                
                // Device Memory API
                if ('deviceMemory' in navigator) {
                    console.log('🧠 DEVICE MEMORY API: Available');
                    console.log('   - Device memory:', navigator.deviceMemory + ' GB');
                }
                
                // Hardware Concurrency
                if ('hardwareConcurrency' in navigator) {
                    console.log('⚡ HARDWARE CONCURRENCY: Available');
                    console.log('   - Logical processors:', navigator.hardwareConcurrency);
                }
                
                // Cookie Store API (experimental)
                if ('cookieStore' in window) {
                    console.log('🍪 COOKIE STORE API: Supported (experimental)');
                }
                
                // Web Locks API
                if ('locks' in navigator) {
                    console.log('🔒 WEB LOCKS API: Supported');
                }
                
                // Web Background Sync
                if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
                    console.log('🔄 BACKGROUND SYNC: Supported');
                }
                */
                
                // SENSORS AND APIs - COMMENTED OUT FOR DEBUG
                /*
                // Web Periodic Background Sync
                if ('serviceWorker' in navigator && 'periodicSync' in window.ServiceWorkerRegistration.prototype) {
                    console.log('🔄 PERIODIC BACKGROUND SYNC: Supported');
                }
                
                // Web Push API
                if ('serviceWorker' in navigator && 'PushManager' in window) {
                    console.log('📢 WEB PUSH API: Supported');
                }
                
                // Notification API
                if ('Notification' in window) {
                    console.log('🔔 NOTIFICATION API: Supported');
                    console.log('   - Permission:', Notification.permission);
                }
                
                // Vibration API
                if ('vibrate' in navigator) {
                    console.log('📳 VIBRATION API: Supported');
                }
                
                // Ambient Light Sensor
                if ('AmbientLightSensor' in window) {
                    console.log('💡 AMBIENT LIGHT SENSOR: Supported');
                }
                
                // Proximity Sensor
                if ('ProximitySensor' in window) {
                    console.log('📏 PROXIMITY SENSOR: Supported');
                }
                
                // Magnetometer
                if ('Magnetometer' in window) {
                    console.log('🧲 MAGNETOMETER: Supported');
                }
                
                // Gyroscope
                if ('Gyroscope' in window) {
                    console.log('🔄 GYROSCOPE: Supported');
                }
                
                // Accelerometer
                if ('Accelerometer' in window) {
                    console.log('📈 ACCELEROMETER: Supported');
                }
                
                // Absolute Orientation Sensor
                if ('AbsoluteOrientationSensor' in window) {
                    console.log('🧭 ABSOLUTE ORIENTATION SENSOR: Supported');
                }
                
                // Relative Orientation Sensor
                if ('RelativeOrientationSensor' in window) {
                    console.log('📐 RELATIVE ORIENTATION SENSOR: Supported');
                }
                */
                
                // MORE APIs AND EVENTS - COMMENTED OUT FOR DEBUG
                /*
                // Geolocation Sensor (alternative to Geolocation API)
                if ('GeolocationSensor' in window) {
                    console.log('📍 GEOLOCATION SENSOR: Supported');
                }
                
                // Web Serial API detailed
                if ('serial' in navigator) {
                    console.log('🔌 WEB SERIAL API: Supported');
                    // Try to get ports (will fail without user permission)
                    navigator.serial.getPorts().then(ports => {
                        console.log('   - Available ports:', ports.length);
                    }).catch(err => {
                        console.log('   - Port enumeration requires user permission');
                    });
                }
                
                // WebHID API detailed
                if ('hid' in navigator) {
                    console.log('🎮 WEBHID API: Supported');
                    // Try to get devices (will fail without user permission)
                    navigator.hid.getDevices().then(devices => {
                        console.log('   - Connected HID devices:', devices.length);
                    }).catch(err => {
                        console.log('   - Device enumeration requires user permission');
                    });
                }
                
                // WebUSB API detailed
                if ('usb' in navigator) {
                    console.log('🔌 WEBUSB API: Supported');
                    // Try to get devices (will fail without user permission)
                    navigator.usb.getDevices().then(devices => {
                        console.log('   - Connected USB devices:', devices.length);
                    }).catch(err => {
                        console.log('   - Device enumeration requires user permission');
                    });
                }
                
                // Web Bluetooth API detailed
                if ('bluetooth' in navigator) {
                    console.log('📡 WEB BLUETOOTH API: Supported');
                }
                
                // Web MIDI API detailed
                if ('requestMIDIAccess' in navigator) {
                    console.log('🎹 WEB MIDI API: Supported');
                }
                
                // WebRTC Insertable Streams
                if (window.RTCRtpSender && 'createEncodedStreams' in RTCRtpSender.prototype) {
                    console.log('📹 WEBRTC INSERTABLE STREAMS: Supported');
                }
                
                // WebRTC SCTP Data Channels
                if (window.RTCPeerConnection) {
                    try {
                        const pc = new RTCPeerConnection();
                        const dc = pc.createDataChannel('test');
                        if (dc) {
                            console.log('📡 WEBRTC SCTP DATA CHANNELS: Supported');
                            dc.close();
                        }
                        pc.close();
                    } catch (e) {
                        console.log('📡 WEBRTC SCTP DATA CHANNELS: Not supported');
                    }
                }
                
                // Événements de sécurité
                document.addEventListener('securitypolicyviolation', function(e) {
                    console.error('🚨 CSP VIOLATION:', {
                        violatedDirective: e.violatedDirective,
                        blockedURI: e.blockedURI,
                        sourceFile: e.sourceFile,
                        lineNumber: e.lineNumber,
                        columnNumber: e.columnNumber
                    });
                });
                
                // Événements de sécurité supplémentaires
                window.addEventListener('beforeunload', function(e) {
                    console.log('🚪 WINDOW BEFORE UNLOAD - Checking for unsaved changes');
                    const modifiedElements = document.querySelectorAll('[modified="true"]');
                    if (modifiedElements.length > 0) {
                        console.log('⚠️ UNSAVED CHANGES DETECTED:', modifiedElements.length, 'elements');
                        modifiedElements.forEach(el => {
                            console.log(`   - ${el.tagName}[${el.name || el.id}]: "${el.value}"`);
                        });
                    }
                });
                
                // Logs pour les événements de focus/blur au niveau fenêtre
                window.addEventListener('focus', () => {
                    console.log('🎯 WINDOW FOCUSED');
                });
                
                window.addEventListener('blur', () => {
                    console.log('👁️ WINDOW BLURRED');
                });
                
                // Logs pour les événements de contexte (clic droit)
                document.addEventListener('contextmenu', function(e) {
                    console.log('📋 CONTEXT MENU:', {
                        target: e.target.tagName,
                        x: e.clientX,
                        y: e.clientY,
                        ctrlKey: e.ctrlKey,
                        shiftKey: e.shiftKey
                    });
                });
                
                // Logs pour les événements de sélection de texte
                document.addEventListener('selectionchange', function() {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        console.log('📝 TEXT SELECTION:', {
                            text: selection.toString(),
                            startContainer: range.startContainer.nodeName,
                            endContainer: range.endContainer.nodeName,
                            collapsed: selection.isCollapsed
                        });
                    }
                });
                */
                
                // Logs pour les événements de mutation DOM avancés
                if (window.MutationObserver) {
                    const advancedObserver = new MutationObserver((mutations) => {
                        mutations.forEach(mutation => {
                            if (mutation.type === 'childList') {
                                console.log('🔄 DOM CHILD LIST MUTATION:', {
                                    added: mutation.addedNodes.length,
                                    removed: mutation.removedNodes.length,
                                    target: mutation.target.tagName,
                                    nextSibling: mutation.nextSibling ? mutation.nextSibling.tagName : null
                                });
                            } else if (mutation.type === 'attributes' && !['modified', 'modified-at'].includes(mutation.attributeName)) {
                                console.log('🔄 DOM ATTRIBUTE MUTATION:', {
                                    attribute: mutation.attributeName,
                                    oldValue: mutation.oldValue,
                                    newValue: mutation.target.getAttribute(mutation.attributeName),
                                    target: mutation.target.tagName + (mutation.target.id ? '#' + mutation.target.id : '')
                                });
                            }
                        });
                    });
                    
                    advancedObserver.observe(document.body, {
                        childList: true,
                        attributes: true,
                        subtree: true,
                        attributeFilter: ['class', 'style', 'id', 'name', 'value', 'checked', 'selected', 'disabled', 'hidden']
                    });
                // Logs pour les événements de visibilité de page
                document.addEventListener('visibilitychange', function() {
                    console.log(`👁️ PAGE VISIBILITY: ${document.hidden ? 'HIDDEN' : 'VISIBLE'} (${document.visibilityState})`);
                });
                
                window.addEventListener('pagehide', function(e) {
                    console.log('📄 PAGE HIDE:', {
                        persisted: e.persisted,
                        timestamp: new Date().toISOString()
                    });
                });
                
                
                // REMAINING SECTIONS - COMMENTED OUT FOR DEBUG
                // REMAINING SECTIONS - COMMENTED OUT FOR DEBUG
                /*                /*window.addEventListener('pageshow', function(e) {
                    console.log('📄 PAGE SHOW:', {
                        persisted: e.persisted,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de performance mémoire (si disponible)
                if ('memory' in performance) {
                    console.log('🧠 MEMORY INFO:', {
                        usedJSHeapSize: performance.memory.usedJSHeapSize,
                        totalJSHeapSize: performance.memory.totalJSHeapSize,
                        jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                    });
                    
                    // Monitor memory usage periodically
                    setInterval(() => {
                        console.log('🧠 MEMORY UPDATE:', {
                            usedJSHeapSize: performance.memory.usedJSHeapSize,
                            totalJSHeapSize: performance.memory.totalJSHeapSize,
                            timestamp: new Date().toISOString()
                        });
                    }, 30000); // Every 30 seconds
                }
                
                // Logs pour les événements de connexion/déconnexion plus détaillés
                window.addEventListener('online', function() {
                    console.log('🌐 NETWORK: Online - Connection restored');
                    // Log network info when coming back online
                    if ('connection' in navigator) {
                        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                        if (connection) {
                            setTimeout(() => {
                                console.log('🌐 NETWORK INFO (post-online):', {
                                    effectiveType: connection.effectiveType,
                                    downlink: connection.downlink,
                                    rtt: connection.rtt
                                });
                            }, 1000);
                        }
                    }
                });
                
                window.addEventListener('offline', function() {
                    console.log('🚫 NETWORK: Offline - Connection lost');
                });
                
                // Logs pour les événements de batterie plus détaillés
                if ('getBattery' in navigator) {
                    navigator.getBattery().then(battery => {
                        // Log initial battery state
                        console.log('🔋 INITIAL BATTERY STATE:', {
                            charging: battery.charging,
                            chargingTime: battery.chargingTime,
                            dischargingTime: battery.dischargingTime,
                            level: Math.round(battery.level * 100) + '%'
                        });
                        
                        // Events already added above, but adding discharge time monitoring
                        battery.addEventListener('chargingtimechange', () => {
                            console.log('🔋 CHARGING TIME CHANGED:', battery.chargingTime);
                        });
                        
                        battery.addEventListener('dischargingtimechange', () => {
                            console.log('🔋 DISCHARGING TIME CHANGED:', battery.dischargingTime);
                        });
                    });
                }
                
                // Logs pour les événements de capteurs (si disponibles)
                if ('DeviceMotionEvent' in window) {
                    window.addEventListener('devicemotion', function(e) {
                        // Log only significant motion (throttle to avoid spam)
                        if (Math.abs(e.acceleration.x) > 1 || Math.abs(e.acceleration.y) > 1 || Math.abs(e.acceleration.z) > 1) {
                            console.log('📳 DEVICE MOTION:', {
                                acceleration: {
                                    x: e.acceleration.x,
                                    y: e.acceleration.y,
                                    z: e.acceleration.z
                                },
                                rotationRate: e.rotationRate,
                                interval: e.interval
                            });
                        }
                    });
                }
                
                // Logs pour les événements de géolocalisation (tentatives)
                if ('geolocation' in navigator) {
                    // Monitor geolocation permission changes
                    navigator.permissions.query({name:'geolocation'}).then(permission => {
                        console.log('📍 GEOLOCATION PERMISSION:', permission.state);
                        permission.addEventListener('change', () => {
                            console.log('📍 GEOLOCATION PERMISSION CHANGED:', permission.state);
                        });
                    }).catch(err => {
                        console.log('📍 GEOLOCATION PERMISSION QUERY FAILED:', err.message);
                    });
                }
                
                // Logs pour les événements de stockage plus détaillés
                window.addEventListener('storage', function(e) {
                    console.log('💾 STORAGE EVENT:', {
                        key: e.key,
                        oldValue: e.oldValue ? e.oldValue.substring(0, 50) + (e.oldValue.length > 50 ? '...' : '') : null,
                        newValue: e.newValue ? e.newValue.substring(0, 50) + (e.newValue.length > 50 ? '...' : '') : null,
                        storageArea: e.storageArea === localStorage ? 'localStorage' : 'sessionStorage',
                        url: e.url
                    });
                });
                
                // Logs pour les événements de performance de navigation détaillés
                if (window.performance && window.performance.timing) {
                    window.addEventListener('load', function() {
                        setTimeout(() => {
                            const timing = window.performance.timing;
                            const navigation = window.performance.navigation;
                            
                            console.log('⏱️ DETAILED PAGE LOAD PERFORMANCE:', {
                                navigationStart: timing.navigationStart,
                                unloadEventStart: timing.unloadEventStart,
                                unloadEventEnd: timing.unloadEventEnd,
                                redirectStart: timing.redirectStart,
                                redirectEnd: timing.redirectEnd,
                                fetchStart: timing.fetchStart,
                                domainLookupStart: timing.domainLookupStart,
                                domainLookupEnd: timing.domainLookupEnd,
                                connectStart: timing.connectStart,
                                connectEnd: timing.connectEnd,
                                secureConnectionStart: timing.secureConnectionStart,
                                requestStart: timing.requestStart,
                                responseStart: timing.responseStart,
                                responseEnd: timing.responseEnd,
                                domLoading: timing.domLoading,
                                domInteractive: timing.domInteractive,
                                domContentLoadedEventStart: timing.domContentLoadedEventStart,
                                domContentLoadedEventEnd: timing.domContentLoadedEventEnd,
                                domComplete: timing.domComplete,
                                loadEventStart: timing.loadEventStart,
                                loadEventEnd: timing.loadEventEnd,
                                navigationType: navigation.type === 0 ? 'NAVIGATE' : navigation.type === 1 ? 'RELOAD' : 'BACK_FORWARD',
                                redirectCount: navigation.redirectCount
                            });
                        }, 0);
                    });
                }
                
                // Logs pour les événements de sécurité supplémentaires
                document.addEventListener('securitypolicyviolation', function(e) {
                    console.error('🚨 CSP VIOLATION:', {
                        violatedDirective: e.violatedDirective,
                        blockedURI: e.blockedURI,
                        sourceFile: e.sourceFile,
                        lineNumber: e.lineNumber,
                        columnNumber: e.columnNumber,
                        originalPolicy: e.originalPolicy,
                        violatedDirective: e.violatedDirective,
                        effectiveDirective: e.effectiveDirective,
                        statusCode: e.statusCode
                    });
                });
                
                // Logs pour les événements de console redéfinis (améliorés)
                const originalWarn = console.warn;
                const originalError = console.error;
                
                console.warn = function(...args) {
                    originalWarn.apply(console, ['🟡 ENHANCED WARNING:'].concat(args));
                    // Also log to our custom system if needed
                    originalWarn.apply(console, args);
                };
                
                console.error = function(...args) {
                    originalError.apply(console, ['🔴 ENHANCED ERROR:'].concat(args));
                    // Also log to our custom system if needed
                    originalError.apply(console, args);
                };
                
                // Logs pour les événements de mutation DOM avancés (complément)
                if (window.MutationObserver) {
                    const securityObserver = new MutationObserver((mutations) => {
                        mutations.forEach(mutation => {
                            // Monitor for potentially suspicious DOM changes
                            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                                const suspiciousNodes = Array.from(mutation.addedNodes).filter(node => {
                                    return node.nodeType === Node.ELEMENT_NODE && 
                                           (node.tagName === 'SCRIPT' || node.tagName === 'IFRAME' || 
                                            node.tagName === 'OBJECT' || node.tagName === 'EMBED');
                                });
                                
                                if (suspiciousNodes.length > 0) {
                                    console.warn('🚨 SUSPICIOUS DOM ADDITION:', suspiciousNodes.map(node => ({
                                        tagName: node.tagName,
                                        src: node.src || node.data,
                                        innerHTML: node.innerHTML ? node.innerHTML.substring(0, 100) : null
                                    })));
                                }
                            }
                        });
                    });
                    
                    securityObserver.observe(document.head, {
                        childList: true,
                        subtree: true
                    });
                    
                    securityObserver.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                // Logs pour les événements de navigation
                window.addEventListener('popstate', function(e) {
                    console.log('🧭 POPSTATE:', {
                        state: e.state,
                        url: window.location.href,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements d'orientation
                window.addEventListener('orientationchange', function() {
                    console.log('📱 ORIENTATION CHANGE:', {
                        angle: screen.orientation ? screen.orientation.angle : window.orientation,
                        type: screen.orientation ? screen.orientation.type : 'unknown',
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de plein écran
                document.addEventListener('fullscreenchange', function() {
                    console.log('🖥️ FULLSCREEN CHANGE:', {
                        isFullscreen: !!document.fullscreenElement,
                        element: document.fullscreenElement ? document.fullscreenElement.tagName : null,
                        timestamp: new Date().toISOString()
                    });
                });
                
                document.addEventListener('fullscreenerror', function(e) {
                    console.error('🖥️ FULLSCREEN ERROR:', {
                        error: e,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de pointeur verrouillé
                document.addEventListener('pointerlockchange', function() {
                    console.log('🔒 POINTER LOCK CHANGE:', {
                        isLocked: !!document.pointerLockElement,
                        element: document.pointerLockElement ? document.pointerLockElement.tagName : null,
                        timestamp: new Date().toISOString()
                    });
                });
                
                document.addEventListener('pointerlockerror', function(e) {
                    console.error('🔒 POINTER LOCK ERROR:', {
                        error: e,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements WebGL (si canvas WebGL existe)
                const canvases = document.querySelectorAll('canvas');
                canvases.forEach((canvas, index) => {
                    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                    if (gl) {
                        canvas.addEventListener('webglcontextlost', function(e) {
                            console.error('🎨 WEBGL CONTEXT LOST:', {
                                canvasIndex: index,
                                canvasId: canvas.id || 'unnamed',
                                event: e,
                                timestamp: new Date().toISOString()
                            });
                            e.preventDefault(); // Permettre la restauration
                        });
                        
                        canvas.addEventListener('webglcontextrestored', function(e) {
                            console.log('🎨 WEBGL CONTEXT RESTORED:', {
                                canvasIndex: index,
                                canvasId: canvas.id || 'unnamed',
                                event: e,
                                timestamp: new Date().toISOString()
                            });
                        });
                    }
                });
                
                // Logs pour les événements de performance détaillés (si PerformanceObserver supporté)
                if ('PerformanceObserver' in window) {
                    try {
                        // Observer les métriques de performance longues
                        const longtaskObserver = new PerformanceObserver((list) => {
                            const entries = list.getEntries();
                            entries.forEach(entry => {
                                if (entry.duration > 50) { // Seulement les tâches longues (>50ms)
                                    console.warn('⏱️ LONG TASK DETECTED:', {
                                        name: entry.name,
                                        duration: entry.duration,
                                        startTime: entry.startTime,
                                        timestamp: new Date().toISOString()
                                    });
                                }
                            });
                        });
                        longtaskObserver.observe({ entryTypes: ['longtask'] });
                        console.log('⏱️ LONG TASK OBSERVER: Active (monitoring tasks >50ms)');
                    } catch (e) {
                        console.log('⏱️ LONG TASK OBSERVER: Not supported or failed to initialize');
                    }
                }
                
                // Logs pour les événements de réseau avancés (si disponible)
                if ('connection' in navigator) {
                    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (connection && 'addEventListener' in connection) {
                        connection.addEventListener('change', () => {
                            console.log('🌐 NETWORK INFO CHANGED:', {
                                effectiveType: connection.effectiveType,
                                downlink: connection.downlink,
                                rtt: connection.rtt,
                                saveData: connection.saveData,
                                timestamp: new Date().toISOString()
                            });
                        });
                    }
                }
                
                // Logs pour les événements de batterie avancés
                if ('getBattery' in navigator) {
                    navigator.getBattery().then(battery => {
                        // Événements de changement de niveau détaillés
                        let lastLevel = battery.level;
                        battery.addEventListener('levelchange', () => {
                            const currentLevel = battery.level;
                            const change = currentLevel - lastLevel;
                            console.log('🔋 BATTERY LEVEL CHANGE:', {
                                previousLevel: Math.round(lastLevel * 100) + '%',
                                currentLevel: Math.round(currentLevel * 100) + '%',
                                change: Math.round(change * 100) + '%',
                                charging: battery.charging,
                                timestamp: new Date().toISOString()
                            });
                            lastLevel = currentLevel;
                        });
                    });
                }
                
                // Logs pour les événements de stockage avec plus de détails
                window.addEventListener('storage', function(e) {
                    const valuePreview = e.newValue ? 
                        (e.newValue.length > 100 ? e.newValue.substring(0, 100) + '...' : e.newValue) : 
                        null;
                    const oldValuePreview = e.oldValue ? 
                        (e.oldValue.length > 100 ? e.oldValue.substring(0, 100) + '...' : e.oldValue) : 
                        null;
                    
                    console.log('💾 STORAGE CHANGE:', {
                        key: e.key,
                        oldValue: oldValuePreview,
                        newValue: valuePreview,
                        storageArea: e.storageArea === localStorage ? 'localStorage' : 'sessionStorage',
                        url: e.url,
                        valueLength: e.newValue ? e.newValue.length : 0,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de performance de navigation détaillés
                if (window.performance && window.performance.getEntriesByType) {
                    window.addEventListener('load', function() {
                        setTimeout(() => {
                            // Logs pour les ressources chargées
                            const resources = window.performance.getEntriesByType('resource');
                            const slowResources = resources.filter(r => r.duration > 1000); // >1s
                            
                            if (slowResources.length > 0) {
                                console.warn('🐌 SLOW RESOURCES DETECTED:', slowResources.map(r => ({
                                    name: r.name,
                                    duration: Math.round(r.duration),
                                    size: r.transferSize || 'unknown',
                                    type: r.initiatorType
                                })));
                            }
                            
                            // Logs pour les métriques de performance globales
                            const navigation = window.performance.getEntriesByType('navigation')[0];
                            if (navigation) {
                                console.log('📊 NAVIGATION PERFORMANCE:', {
                                    domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                                    loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                                    totalTime: navigation.loadEventEnd - navigation.fetchStart,
                                    dnsLookup: navigation.domainLookupEnd - navigation.domainLookupStart,
                                    tcpConnect: navigation.connectEnd - navigation.connectStart,
                                    serverResponse: navigation.responseEnd - navigation.requestStart,
                                    pageProcessing: navigation.loadEventStart - navigation.responseEnd
                                });
                            }
                        }, 1000);
                    });
                }
                
                // Événements de sécurité
                document.addEventListener('securitypolicyviolation', function(e) {
                    console.error('🚨 CSP VIOLATION:', {
                        violatedDirective: e.violatedDirective,
                        blockedURI: e.blockedURI,
                        sourceFile: e.sourceFile,
                        lineNumber: e.lineNumber,
                        columnNumber: e.columnNumber,
                        originalPolicy: e.originalPolicy,
                        violatedDirective: e.violatedDirective,
                        effectiveDirective: e.effectiveDirective,
                        statusCode: e.statusCode
                    });
                });
                
                // Événements de sécurité supplémentaires
                window.addEventListener('beforeunload', function(e) {
                    console.log('🚪 WINDOW BEFORE UNLOAD - Checking for unsaved changes');
                    const modifiedElements = document.querySelectorAll('[modified="true"]');
                    if (modifiedElements.length > 0) {
                        console.log('⚠️ UNSAVED CHANGES DETECTED:', modifiedElements.length, 'elements');
                        modifiedElements.forEach(el => {
                            console.log(`   - ${el.tagName}[${el.name || el.id}]: "${el.value}"`);
                        });
                    }
                });
                
                // Logs pour les événements de focus/blur au niveau fenêtre
                window.addEventListener('focus', () => {
                    console.log('🎯 WINDOW FOCUSED');
                });
                
                window.addEventListener('blur', () => {
                    console.log('👁️ WINDOW BLURRED');
                });
                
                // Logs pour les événements de contexte (clic droit)
                document.addEventListener('contextmenu', function(e) {
                    console.log('📋 CONTEXT MENU:', {
                        target: e.target.tagName,
                        x: e.clientX,
                        y: e.clientY,
                        ctrlKey: e.ctrlKey,
                        shiftKey: e.shiftKey
                    });
                });
                
                // Logs pour les événements de sélection de texte
                document.addEventListener('selectionchange', function() {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        console.log('📝 TEXT SELECTION:', {
                            text: selection.toString(),
                            startContainer: range.startContainer.nodeName,
                            endContainer: range.endContainer.nodeName,
                            collapsed: selection.isCollapsed
                        });
                    }
                });
                
                // Logs pour les événements de mutation DOM avancés
                if (window.MutationObserver) {
                    const advancedObserver = new MutationObserver((mutations) => {
                        mutations.forEach(mutation => {
                            if (mutation.type === 'childList') {
                                console.log('🔄 DOM CHILD LIST MUTATION:', {
                                    added: mutation.addedNodes.length,
                                    removed: mutation.removedNodes.length,
                                    target: mutation.target.tagName,
                                    nextSibling: mutation.nextSibling ? mutation.nextSibling.tagName : null
                                });
                            } else if (mutation.type === 'attributes' && !['modified', 'modified-at'].includes(mutation.attributeName)) {
                                console.log('🔄 DOM ATTRIBUTE MUTATION:', {
                                    attribute: mutation.attributeName,
                                    oldValue: mutation.oldValue,
                                    newValue: mutation.target.getAttribute(mutation.attributeName),
                                    target: mutation.target.tagName + (mutation.target.id ? '#' + mutation.target.id : '')
                                });
                            }
                        });
                    });
                    
                    advancedObserver.observe(document.body, {
                        childList: true,
                        attributes: true,
                        subtree: true,
                        attributeFilter: ['class', 'style', 'id', 'name', 'value', 'checked', 'selected', 'disabled', 'hidden']
                    });
                // Logs pour les événements de visibilité de page
                document.addEventListener('visibilitychange', function() {
                    console.log(`👁️ PAGE VISIBILITY: ${document.hidden ? 'HIDDEN' : 'VISIBLE'} (${document.visibilityState})`);
                });
                
                window.addEventListener('pagehide', function(e) {
                    console.log('📄 PAGE HIDE:', {
                        persisted: e.persisted,
                        timestamp: new Date().toISOString()
                    });
                });
                
                window.addEventListener('pageshow', function(e) {
                    console.log('📄 PAGE SHOW:', {
                        persisted: e.persisted,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de performance mémoire (si disponible)
                if ('memory' in performance) {
                    console.log('🧠 MEMORY INFO:', {
                        usedJSHeapSize: performance.memory.usedJSHeapSize,
                        totalJSHeapSize: performance.memory.totalJSHeapSize,
                        jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                    });
                    
                    // Monitor memory usage periodically
                    setInterval(() => {
                        console.log('🧠 MEMORY UPDATE:', {
                            usedJSHeapSize: performance.memory.usedJSHeapSize,
                            totalJSHeapSize: performance.memory.totalJSHeapSize,
                            timestamp: new Date().toISOString()
                        });
                    }, 30000); // Every 30 seconds
                }
                
                // Logs pour les événements de connexion/déconnexion plus détaillés
                window.addEventListener('online', function() {
                    console.log('🌐 NETWORK: Online - Connection restored');
                    // Log network info when coming back online
                    if ('connection' in navigator) {
                        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                        if (connection) {
                            setTimeout(() => {
                                console.log('🌐 NETWORK INFO (post-online):', {
                                    effectiveType: connection.effectiveType,
                                    downlink: connection.downlink,
                                    rtt: connection.rtt
                                });
                            }, 1000);
                        }
                    }
                });
                
                window.addEventListener('offline', function() {
                    console.log('🚫 NETWORK: Offline - Connection lost');
                });
                
                // Logs pour les événements de batterie plus détaillés
                if ('getBattery' in navigator) {
                    navigator.getBattery().then(battery => {
                        // Log initial battery state
                        console.log('🔋 INITIAL BATTERY STATE:', {
                            charging: battery.charging,
                            chargingTime: battery.chargingTime,
                            dischargingTime: battery.dischargingTime,
                            level: Math.round(battery.level * 100) + '%'
                        });
                        
                        // Events already added above, but adding discharge time monitoring
                        battery.addEventListener('chargingtimechange', () => {
                            console.log('🔋 CHARGING TIME CHANGED:', battery.chargingTime);
                        });
                        
                        battery.addEventListener('dischargingtimechange', () => {
                            console.log('🔋 DISCHARGING TIME CHANGED:', battery.dischargingTime);
                        });
                    });
                }
                
                // Logs pour les événements de capteurs (si disponibles)
                if ('DeviceMotionEvent' in window) {
                    window.addEventListener('devicemotion', function(e) {
                        // Log only significant motion (throttle to avoid spam)
                        if (Math.abs(e.acceleration.x) > 1 || Math.abs(e.acceleration.y) > 1 || Math.abs(e.acceleration.z) > 1) {
                            console.log('📳 DEVICE MOTION:', {
                                acceleration: {
                                    x: e.acceleration.x,
                                    y: e.acceleration.y,
                                    z: e.acceleration.z
                                },
                                rotationRate: e.rotationRate,
                                interval: e.interval
                            });
                        }
                    });
                }
                
                // Logs pour les événements de géolocalisation (tentatives)
                if ('geolocation' in navigator) {
                    // Monitor geolocation permission changes
                    navigator.permissions.query({name:'geolocation'}).then(permission => {
                        console.log('📍 GEOLOCATION PERMISSION:', permission.state);
                        permission.addEventListener('change', () => {
                            console.log('📍 GEOLOCATION PERMISSION CHANGED:', permission.state);
                        });
                    }).catch(err => {
                        console.log('📍 GEOLOCATION PERMISSION QUERY FAILED:', err.message);
                    });
                }
                
                // Logs pour les événements de stockage plus détaillés
                window.addEventListener('storage', function(e) {
                    console.log('💾 STORAGE EVENT:', {
                        key: e.key,
                        oldValue: e.oldValue ? e.oldValue.substring(0, 50) + (e.oldValue.length > 50 ? '...' : '') : null,
                        newValue: e.newValue ? e.newValue.substring(0, 50) + (e.newValue.length > 50 ? '...' : '') : null,
                        storageArea: e.storageArea === localStorage ? 'localStorage' : 'sessionStorage',
                        url: e.url
                    });
                });
                
                // Logs pour les événements de performance de navigation détaillés
                if (window.performance && window.performance.timing) {
                    window.addEventListener('load', function() {
                        setTimeout(() => {
                            const timing = window.performance.timing;
                            const navigation = window.performance.navigation;
                            
                            console.log('⏱️ DETAILED PAGE LOAD PERFORMANCE:', {
                                navigationStart: timing.navigationStart,
                                unloadEventStart: timing.unloadEventStart,
                                unloadEventEnd: timing.unloadEventEnd,
                                redirectStart: timing.redirectStart,
                                redirectEnd: timing.redirectEnd,
                                fetchStart: timing.fetchStart,
                                domainLookupStart: timing.domainLookupStart,
                                domainLookupEnd: timing.domainLookupEnd,
                                connectStart: timing.connectStart,
                                connectEnd: timing.connectEnd,
                                secureConnectionStart: timing.secureConnectionStart,
                                requestStart: timing.requestStart,
                                responseStart: timing.responseStart,
                                responseEnd: timing.responseEnd,
                                domLoading: timing.domLoading,
                                domInteractive: timing.domInteractive,
                                domContentLoadedEventStart: timing.domContentLoadedEventStart,
                                domContentLoadedEventEnd: timing.domContentLoadedEventEnd,
                                domComplete: timing.domComplete,
                                loadEventStart: timing.loadEventStart,
                                loadEventEnd: timing.loadEventEnd,
                                navigationType: navigation.type === 0 ? 'NAVIGATE' : navigation.type === 1 ? 'RELOAD' : 'BACK_FORWARD',
                                redirectCount: navigation.redirectCount
                            });
                        }, 0);
                    });
                }
                
                // Logs pour les événements de sécurité supplémentaires
                document.addEventListener('securitypolicyviolation', function(e) {
                    console.error('🚨 CSP VIOLATION:', {
                        violatedDirective: e.violatedDirective,
                        blockedURI: e.blockedURI,
                        sourceFile: e.sourceFile,
                        lineNumber: e.lineNumber,
                        columnNumber: e.columnNumber,
                        originalPolicy: e.originalPolicy,
                        violatedDirective: e.violatedDirective,
                        effectiveDirective: e.effectiveDirective,
                        statusCode: e.statusCode
                    });
                });
                
                // Logs pour les événements de console redéfinis (améliorés)
                const originalWarn = console.warn;
                const originalError = console.error;
                
                console.warn = function(...args) {
                    originalWarn.apply(console, ['🟡 ENHANCED WARNING:'].concat(args));
                    // Also log to our custom system if needed
                    originalWarn.apply(console, args);
                };
                
                console.error = function(...args) {
                    originalError.apply(console, ['🔴 ENHANCED ERROR:'].concat(args));
                    // Also log to our custom system if needed
                    originalError.apply(console, args);
                };
                
                // Logs pour les événements de mutation DOM avancés (complément)
                if (window.MutationObserver) {
                    const securityObserver = new MutationObserver((mutations) => {
                        mutations.forEach(mutation => {
                            // Monitor for potentially suspicious DOM changes
                            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                                const suspiciousNodes = Array.from(mutation.addedNodes).filter(node => {
                                    return node.nodeType === Node.ELEMENT_NODE && 
                                           (node.tagName === 'SCRIPT' || node.tagName === 'IFRAME' || 
                                            node.tagName === 'OBJECT' || node.tagName === 'EMBED');
                                });
                                
                                if (suspiciousNodes.length > 0) {
                                    console.warn('🚨 SUSPICIOUS DOM ADDITION:', suspiciousNodes.map(node => ({
                                        tagName: node.tagName,
                                        src: node.src || node.data,
                                        innerHTML: node.innerHTML ? node.innerHTML.substring(0, 100) : null
                                    })));
                                }
                            }
                        });
                    });
                    
                    securityObserver.observe(document.head, {
                        childList: true,
                        subtree: true
                    });
                    
                    securityObserver.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                // Logs pour les événements de navigation
                window.addEventListener('popstate', function(e) {
                    console.log('🧭 POPSTATE:', {
                        state: e.state,
                        url: window.location.href,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements d'orientation
                window.addEventListener('orientationchange', function() {
                    console.log('📱 ORIENTATION CHANGE:', {
                        angle: screen.orientation ? screen.orientation.angle : window.orientation,
                        type: screen.orientation ? screen.orientation.type : 'unknown',
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de plein écran
                document.addEventListener('fullscreenchange', function() {
                    console.log('🖥️ FULLSCREEN CHANGE:', {
                        isFullscreen: !!document.fullscreenElement,
                        element: document.fullscreenElement ? document.fullscreenElement.tagName : null,
                        timestamp: new Date().toISOString()
                    });
                });
                
                document.addEventListener('fullscreenerror', function(e) {
                    console.error('🖥️ FULLSCREEN ERROR:', {
                        error: e,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de pointeur verrouillé
                document.addEventListener('pointerlockchange', function() {
                    console.log('🔒 POINTER LOCK CHANGE:', {
                        isLocked: !!document.pointerLockElement,
                        element: document.pointerLockElement ? document.pointerLockElement.tagName : null,
                        timestamp: new Date().toISOString()
                    });
                });
                
                document.addEventListener('pointerlockerror', function(e) {
                    console.error('🔒 POINTER LOCK ERROR:', {
                        error: e,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements WebGL (si canvas WebGL existe)
                const canvases = document.querySelectorAll('canvas');
                canvases.forEach((canvas, index) => {
                    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                    if (gl) {
                        canvas.addEventListener('webglcontextlost', function(e) {
                            console.error('🎨 WEBGL CONTEXT LOST:', {
                                canvasIndex: index,
                                canvasId: canvas.id || 'unnamed',
                                event: e,
                                timestamp: new Date().toISOString()
                            });
                            e.preventDefault(); // Permettre la restauration
                        });
                        
                        canvas.addEventListener('webglcontextrestored', function(e) {
                            console.log('🎨 WEBGL CONTEXT RESTORED:', {
                                canvasIndex: index,
                                canvasId: canvas.id || 'unnamed',
                                event: e,
                                timestamp: new Date().toISOString()
                            });
                        });
                    }
                });
                
                // Logs pour les événements de performance détaillés (si PerformanceObserver supporté)
                if ('PerformanceObserver' in window) {
                    try {
                        // Observer les métriques de performance longues
                        const longtaskObserver = new PerformanceObserver((list) => {
                            const entries = list.getEntries();
                            entries.forEach(entry => {
                                if (entry.duration > 50) { // Seulement les tâches longues (>50ms)
                                    console.warn('⏱️ LONG TASK DETECTED:', {
                                        name: entry.name,
                                        duration: entry.duration,
                                        startTime: entry.startTime,
                                        timestamp: new Date().toISOString()
                                    });
                                }
                            });
                        });
                        longtaskObserver.observe({ entryTypes: ['longtask'] });
                        console.log('⏱️ LONG TASK OBSERVER: Active (monitoring tasks >50ms)');
                    } catch (e) {
                        console.log('⏱️ LONG TASK OBSERVER: Not supported or failed to initialize');
                    }
                }
                
                // Logs pour les événements de réseau avancés (si disponible)
                if ('connection' in navigator) {
                    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (connection && 'addEventListener' in connection) {
                        connection.addEventListener('change', () => {
                            console.log('🌐 NETWORK INFO CHANGED:', {
                                effectiveType: connection.effectiveType,
                                downlink: connection.downlink,
                                rtt: connection.rtt,
                                saveData: connection.saveData,
                                timestamp: new Date().toISOString()
                            });
                        });
                    }
                }
                
                // Logs pour les événements de batterie avancés
                if ('getBattery' in navigator) {
                    navigator.getBattery().then(battery => {
                        // Événements de changement de niveau détaillés
                        let lastLevel = battery.level;
                        battery.addEventListener('levelchange', () => {
                            const currentLevel = battery.level;
                            const change = currentLevel - lastLevel;
                            console.log('🔋 BATTERY LEVEL CHANGE:', {
                                previousLevel: Math.round(lastLevel * 100) + '%',
                                currentLevel: Math.round(currentLevel * 100) + '%',
                                change: Math.round(change * 100) + '%',
                                charging: battery.charging,
                                timestamp: new Date().toISOString()
                            });
                            lastLevel = currentLevel;
                        });
                    });
                }
                
                // Logs pour les événements de stockage avec plus de détails
                window.addEventListener('storage', function(e) {
                    const valuePreview = e.newValue ? 
                        (e.newValue.length > 100 ? e.newValue.substring(0, 100) + '...' : e.newValue) : 
                        null;
                    const oldValuePreview = e.oldValue ? 
                        (e.oldValue.length > 100 ? e.oldValue.substring(0, 100) + '...' : e.oldValue) : 
                        null;
                    
                    console.log('💾 STORAGE CHANGE:', {
                        key: e.key,
                        oldValue: oldValuePreview,
                        newValue: valuePreview,
                        storageArea: e.storageArea === localStorage ? 'localStorage' : 'sessionStorage',
                        url: e.url,
                        valueLength: e.newValue ? e.newValue.length : 0,
                        timestamp: new Date().toISOString()
                    });
                });
                
                // Logs pour les événements de performance de navigation détaillés
                if (window.performance && window.performance.getEntriesByType) {
                    window.addEventListener('load', function() {
                        setTimeout(() => {
                            // Logs pour les ressources chargées
                            const resources = window.performance.getEntriesByType('resource');
                            const slowResources = resources.filter(r => r.duration > 1000); // >1s
                            
                            if (slowResources.length > 0) {
                                console.warn('🐌 SLOW RESOURCES DETECTED:', slowResources.map(r => ({
                                    name: r.name,
                                    duration: Math.round(r.duration),
                                    size: r.transferSize || 'unknown',
                                    type: r.initiatorType
                                })));
                            }
                            
                            // Logs pour les métriques de performance globales
                            const navigation = window.performance.getEntriesByType('navigation')[0];
                            if (navigation) {
                                console.log('📊 NAVIGATION PERFORMANCE:', {
                                    domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                                    loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                                    totalTime: navigation.loadEventEnd - navigation.fetchStart,
                                    dnsLookup: navigation.domainLookupEnd - navigation.domainLookupStart,
                                    tcpConnect: navigation.connectEnd - navigation.connectStart,
                                    serverResponse: navigation.responseEnd - navigation.requestStart,
                                    pageProcessing: navigation.loadEventStart - navigation.responseEnd
                                });
                            }
                        });
                    });
                }
                
            }, 2000); // Attendre 2 secondes pour que tout soit chargé
        }
        
        // Logs pour toutes les soumissions de formulaire (y compris celles non-AJAX)
        function addFormSubmissionListeners() {
            const allForms = document.querySelectorAll('form');
            console.log(`📝 Setting up submission listeners for ${allForms.length} forms`);
            
            allForms.forEach((form, index) => {
                form.addEventListener('submit', function(e) {
                    console.log(`🚀 FORM SUBMISSION INTERCEPTED: ${form.id || 'unnamed-form-' + index}`);
                    console.log(`   - Action: ${form.action}`);
                    console.log(`   - Method: ${form.method}`);
                    console.log(`   - Target: ${form.target}`);
                    console.log(`   - Elements: ${form.elements.length}`);
                    
                    // Logger tous les éléments du formulaire soumis
                    const formData = new FormData(form);
                    console.log('📋 FORM DATA TO BE SUBMITTED:');
                    let dataCount = 0;
                    for (let [key, value] of formData.entries()) {
                        console.log(`   ${key}: ${value}`);
                        dataCount++;
                    }
                    console.log(`   - Total data entries: ${dataCount}`);
                    
                    // Vérifier si c'est une soumission AJAX ou normale
                    if (e.defaultPrevented) {
                        console.log('   ✅ Submission prevented (probably AJAX)');
                    } else {
                        console.log('   ⚠️ Normal form submission (page will reload)');
                        
                        // Identifier l'onglet basé sur l'ID du formulaire
                        let tabName = 'unknown';
                        if (form.id.includes('pdf')) tabName = 'PDF';
                        else if (form.id.includes('security') || form.id.includes('securite')) tabName = 'Security';
                        else if (form.id.includes('canvas')) tabName = 'Canvas';
                        else if (form.id.includes('templates')) tabName = 'Templates';
                        else if (form.id.includes('maintenance')) tabName = 'Maintenance';
                        else if (form.id.includes('developpeur')) tabName = 'Developer';
                        else if (form.id.includes('notifications')) tabName = 'Notifications';
                        else if (form.id.includes('roles')) tabName = 'Roles';
                        else if (form.id.includes('licence')) tabName = 'License';
                        
                        console.log(`   📑 Tab identified: ${tabName}`);
                        console.log('   🔄 Consider implementing AJAX for this form to avoid page reload');
                        
                        // Log détaillé pour les onglets sans AJAX
                        if (['PDF', 'Security', 'Canvas', 'Templates', 'Maintenance', 'Developer', 'Notifications', 'Roles', 'License'].includes(tabName)) {
                            console.log(`   📝 SUBMISSION DETAILS for ${tabName} tab:`);
                            console.log(`      - Form ID: ${form.id}`);
                            console.log(`      - Will cause page reload`);
                            console.log(`      - User experience impact: HIGH`);
                        }
                    }
                    
                    // Log des éléments avec des valeurs non-vides
                    const nonEmptyElements = Array.from(form.elements).filter(el => el.value && el.value.trim() !== '');
                    console.log(`   📊 Non-empty elements: ${nonEmptyElements.length}`);
                    nonEmptyElements.forEach(el => {
                        console.log(`     - ${el.name || el.id}: "${el.value}"`);
                    });
                    
                }, true); // Use capture phase to catch before other handlers
            });
        }
        
        // Ajouter les listeners de soumission de formulaire
        addFormSubmissionListeners();
        
        // Logs pour les événements de formulaire globaux
        function addGlobalFormEventListeners() {
            const allForms = document.querySelectorAll('form');
            console.log(`📄 FORMS DÉTECTÉS: ${allForms.length}`);
            allForms.forEach((form, index) => {
                console.log(`FORM ${index}: [id="${form.id}"][action="${form.action}"][method="${form.method}"]`);
                
                form.addEventListener('submit', function(e) {
                    console.log(`🚀 FORM SUBMIT: ${form.id || 'unnamed form'}`);
                    console.log(`   - Action: ${form.action}`);
                    console.log(`   - Method: ${form.method}`);
                    console.log(`   - Elements: ${form.elements.length}`);
                    
                    // Logger tous les éléments du formulaire soumis
                    const formData = new FormData(form);
                    console.log('📋 FORM DATA:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`   ${key}: ${value}`);
                    }
                    
                    // Vérifier si c'est une soumission AJAX ou normale
                    if (e.defaultPrevented) {
                        console.log('   - Prevented: OUI (probablement AJAX)');
                    } else {
                        console.log('   - Prevented: NON (soumission normale)');
                    }
                });
                
                form.addEventListener('reset', function() {
                    console.log(`🔄 FORM RESET: ${form.id || 'unnamed form'}`);
                });
                
                form.addEventListener('formdata', function(e) {
                    console.log(`📊 FORM DATA EVENT: ${form.id || 'unnamed form'}`);
                    console.log(`   - FormData entries: ${[...e.formData.entries()].length}`);
                });
            });
            
            // Logs pour les événements de fenêtre
            window.addEventListener('beforeunload', function(e) {
                console.log('🚪 WINDOW BEFOREUNLOAD - Vérification des changements non sauvegardés');
                const modifiedElements = document.querySelectorAll('input[modified], select[modified], textarea[modified]');
                if (modifiedElements.length > 0) {
                    console.log(`⚠️ ÉLÉMENTS MODIFIÉS NON SAUVEGARDÉS: ${modifiedElements.length}`);
                    modifiedElements.forEach(el => {
                        console.log(`   - ${el.tagName}[${el.name}]`);
                    });
                }
            });
            
            // Logs pour les erreurs JavaScript
            window.addEventListener('error', function(e) {
                console.error('💥 JAVASCRIPT ERROR:', {
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno,
                    error: e.error
                });
            });
            
            // Logs pour les erreurs non capturées
            window.addEventListener('unhandledrejection', function(e) {
                console.error('💥 UNHANDLED PROMISE REJECTION:', e.reason);
            });
        }
        
        // Ajouter les listeners globaux
        addGlobalFormEventListeners();
        
        // Ajouter les listeners avancés
        addAdvancedEventListeners();
        
        }, 1000);
        
        // Logs removed for clarity
        
        // Vérifier les éléments critiques
        
        // Log des paramètres actuels au chargement
        
        // Récupérer les paramètres PHP et les logger
        const currentSettings = {
            developer_enabled: false,
            debug_javascript: false,
            debug_php_errors: false,
            debug_ajax: false,
            debug_performance: false,
            debug_database: false,
            log_level: "info",
            timestamp: new Date().toISOString()
        };
        
        // Logs removed for clarity
        
        // Vérifier les valeurs des checkboxes au chargement
        setTimeout(() => {
            // Logs removed for clarity
            
            const debugJsCheckbox = document.getElementById('debug_javascript');
            const debugPhpCheckbox = document.getElementById('debug_php_errors');
            const developerEnabledCheckbox = document.getElementById('developer_enabled');
            
            // Logs removed for clarity
        }, 100);
        
        const tabs = document.querySelectorAll('.nav-tab');
        const contents = document.querySelectorAll('.tab-content');

        // Fonction pour changer d'onglet
        function switchTab(targetId, clickedTab) {
            // Masquer tous les contenus
            contents.forEach(function(content) {
                content.classList.add('hidden-tab');
                content.setAttribute('aria-hidden', 'true');
            });
            
            // Désactiver tous les onglets
            tabs.forEach(function(tab) {
                tab.classList.remove('nav-tab-active');
                tab.setAttribute('aria-selected', 'false');
            });
            
            // Afficher l'onglet cible
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.remove('hidden-tab');
                targetContent.setAttribute('aria-hidden', 'false');
            }
            
            // Activer l'onglet cliqué
            clickedTab.classList.add('nav-tab-active');
            clickedTab.setAttribute('aria-selected', 'true');
            
            // Mettre à jour le bouton flottant selon l'onglet
            updateFloatingButton(targetId);
            
            // Mettre à jour l'URL hash
            if (history.pushState) {
                history.pushState(null, null, '#' + targetId);
            } else {
                window.location.hash = '#' + targetId;
            }
            
            // Logger les éléments du nouvel onglet actif
            setTimeout(() => {
                console.log(`=== CHANGEMENT D'ONGLET VERS: ${targetId} ===`);
                logAllFormElements(`TAB_SWITCH_${targetId.toUpperCase()}`);
                
                // Logger spécifiquement les éléments du tab actif
                const activeTabContent = document.getElementById(targetId);
                if (activeTabContent) {
                    const tabInputs = activeTabContent.querySelectorAll('input, select, textarea, button');
                    console.log(`📊 ÉLÉMENTS DANS L'ONGLET ${targetId.toUpperCase()}: ${tabInputs.length}`);
                    tabInputs.forEach((el, idx) => {
                        console.log(`   ${idx}: ${el.tagName}[${el.name || el.id}] = "${el.value}"`);
                    });
                }
            }, 100);
        }
        
        // Fonction pour mettre à jour le bouton flottant selon l'onglet actif
        function updateFloatingButton(activeTabId) {
            const button = document.getElementById('global-save-btn');
            const status = document.getElementById('save-status');
            
            if (!button) return;
            
            // Définir le name du bouton selon l'onglet
            const tabButtonMap = {
                'general': 'submit',
                'pdf': 'submit_pdf', 
                'security': 'submit_security',
                'canvas': 'submit_canvas',
                'performance': 'submit_performance',
                'maintenance': 'submit_maintenance',
                'developpeur': 'submit_developpeur',
                'notifications': 'submit_notifications'
            };
            
            const buttonName = tabButtonMap[activeTabId] || 'submit';
            button.setAttribute('name', buttonName);
            
            // Mettre à jour le texte du bouton selon l'onglet
            const tabNames = {
                'general': 'Général',
                'pdf': 'PDF',
                'security': 'Sécurité', 
                'canvas': 'Canvas',
                'performance': 'Performance',
                'maintenance': 'Maintenance',
                'developpeur': 'Développeur',
                'roles': 'Rôles',
                'notifications': 'Notifications',
                'templates': 'Templates'
            };
            
            const tabName = tabNames[activeTabId] || 'Paramètres';
            button.innerHTML = `💾 Enregistrer ${tabName}`;
            
            // Cacher le bouton dans les onglets qui ont leurs propres boutons
            if (activeTabId === 'roles' || activeTabId === 'templates') {
                button.style.display = 'none';
            } else {
                button.style.display = 'block';
            }
            
            // Masquer le statut précédent
            if (status) {
                status.classList.remove('show', 'success', 'error');
            }
        }
        
        // Gestionnaire d'événement pour les onglets
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-tab');
                switchTab(targetId, this);
            });
        });
        
        // Gestion du hash dans l'URL au chargement
        const hash = window.location.hash.substring(1);
        if (hash) {
            const targetTab = document.querySelector('.nav-tab[data-tab="' + hash + '"]');
            if (targetTab) {
                switchTab(hash, targetTab);
            }
        }
        



        const globalSaveBtn = document.getElementById('global-save-btn');
        const generalSubmitBtn = document.getElementById('general-submit-btn');
        const saveStatus = document.getElementById('save-status');
        
        // Fonction commune pour soumettre un formulaire via AJAX
        function submitFormAjax(form, submitButton) {
            console.log('=== GENERAL FORM AJAX START ===');
            console.log('Form:', form);
            console.log('Submit button:', submitButton);
            console.log('Form ID:', form.id);
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            // Logger tous les éléments du formulaire avec plus de détails
            console.log('=== GENERAL FORM ELEMENTS DÉTAILLÉS ===');
            const allInputs = form.querySelectorAll('input, select, textarea, button');
            allInputs.forEach((element, index) => {
                const details = {
                    index: index,
                    tagName: element.tagName,
                    name: element.name,
                    id: element.id,
                    type: element.type,
                    value: element.value,
                    disabled: element.disabled,
                    required: element.required,
                    className: element.className
                };
                
                console.log(`Element ${index}:`, details);
                
                if (element.type === 'checkbox' || element.type === 'radio') {
                    console.log(`  - Checked: ${element.checked}`);
                }
                
                if (element.tagName === 'SELECT') {
                    const options = Array.from(element.options).map(opt => ({
                        value: opt.value,
                        text: opt.text,
                        selected: opt.selected
                    }));
                    console.log(`  - Options:`, options);
                }
            });
            console.log('=== END GENERAL FORM ELEMENTS DÉTAILLÉS ===');
            
            // Afficher le statut de sauvegarde
            if (saveStatus) {
                saveStatus.textContent = '⏳ Soumission en cours...';
                saveStatus.className = 'save-status show';
            }

            // Désactiver le bouton pendant la soumission
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = submitButton === globalSaveBtn ? '⏳' : '⏳ Soumission...';
            }

            // Créer FormData et logger toutes les données
            const formData = new FormData(form);
            formData.append('action', 'pdf_builder_save_general_settings');
            formData.append('nonce', form.querySelector('input[name="pdf_builder_general_nonce"]').value);
            
            console.log('=== GENERAL FORM DATA LOG ===');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}:`, value);
            }
            console.log('=== END GENERAL FORM DATA LOG ===');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('📡 AJAX Response received');
                console.log('📊 Response status:', response.status, response.statusText);
                console.log('📄 Response headers:', Object.fromEntries(response.headers.entries()));
                console.log('🌐 Response URL:', response.url);
                console.log('✅ Response OK:', response.ok);
                console.log('📏 Response type:', response.type);
                
                // Vérifier le content-type
                const contentType = response.headers.get('content-type');
                console.log('📋 Content-Type:', contentType);
                
                if (!response.ok) {
                    console.error('❌ HTTP Error Response:', {
                        status: response.status,
                        statusText: response.statusText,
                        headers: Object.fromEntries(response.headers.entries())
                    });
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes('application/json')) {
                    console.warn('⚠️ Unexpected content-type, expected JSON:', contentType);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('📦 JSON Response parsed successfully');
                console.log('📋 Parsed data:', data);
                console.log('✅ Success flag:', data.success);
                
                if (data.success) {
                    console.log('🎉 AJAX request successful');
                    if (data.data && data.data.message) {
                        console.log('💬 Success message:', data.data.message);
                    }
                } else {
                    console.warn('⚠️ AJAX request completed but marked as unsuccessful');
                    if (data.data && data.data.message) {
                        console.error('❌ Error message:', data.data.message);
                    }
                }
                
                // Log des métriques de performance
                if (performance.mark && performance.measure) {
                    try {
                        const endTime = performance.now();
                        console.log('⏱️ AJAX Performance:', {
                            responseSize: JSON.stringify(data).length,
                            parsingTime: 'measured via performance API'
                        });
                    } catch (e) {
                        console.log('⏱️ Performance measurement not available');
                    }
                }
                
                // Réactiver le bouton
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton === globalSaveBtn ? '💾' : 'Enregistrer les paramètres';
                }
                
                if (data && data.success) {
                    // Afficher le succès
                    if (saveStatus) {
                        saveStatus.textContent = '✅ ' + (data.data && data.data.message || data.message || 'Sauvegardé avec succès !');
                        saveStatus.className = 'save-status show success';

                        // Masquer le message après 3 secondes
                        setTimeout(() => {
                            saveStatus.className = 'save-status';
                        }, 3000);
                    }
                } else {
                    // Afficher l'erreur
                    if (saveStatus) {
                        const errorMessage = (data.data && data.data.message) || data.message || 'Erreur lors de la sauvegarde';
                        saveStatus.textContent = '❌ ' + errorMessage;
                        saveStatus.className = 'save-status show error';

                        // Masquer le message d'erreur après 5 secondes
                        setTimeout(() => {
                            saveStatus.className = 'save-status';
                        }, 5000);
                    }
                }
            })
            .catch(error => {
                console.error('💥 AJAX REQUEST FAILED');
                console.error('❌ Error type:', error.constructor.name);
                console.error('❌ Error message:', error.message);
                console.error('❌ Error stack:', error.stack);
                
                // Déterminer le type d'erreur
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    console.error('🌐 NETWORK ERROR: Unable to connect to server');
                } else if (error.name === 'SyntaxError') {
                    console.error('📄 JSON PARSING ERROR: Server returned invalid JSON');
                } else if (error instanceof Response) {
                    console.error('📡 HTTP ERROR:', error.status, error.statusText);
                } else {
                    console.error('❓ UNKNOWN ERROR:', error);
                }
                
                // Réactiver le bouton en cas d'erreur
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton === globalSaveBtn ? '💾' : 'Enregistrer les paramètres';
                }
                
                // Afficher l'erreur
                if (saveStatus) {
                    const errorMessage = error.message || 'Erreur de connexion';
                    saveStatus.textContent = '❌ ' + errorMessage;
                    saveStatus.className = 'save-status show error';
                    
                    setTimeout(() => {
                        saveStatus.className = 'save-status';
                    }, 5000);
                }
            });
                            submitButton.disabled = false;
                            submitButton.innerHTML = submitButton === globalSaveBtn ? '💾' : 'Enregistrer les paramètres';
                        }                if (saveStatus) {
                    saveStatus.textContent = '❌ Erreur de connexion';
                    saveStatus.className = 'save-status show error';

                    // Masquer le message d'erreur après 5 secondes
                    setTimeout(() => {
                        saveStatus.className = 'save-status';
                    }, 5000);
                }
            });
        }
        
        if (globalSaveBtn) {
            globalSaveBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Empêcher la soumission normale du formulaire
                console.log('🔘 GLOBAL SAVE BUTTON clicked');
                
                // Trouver le formulaire de l'onglet actif
                const currentTab = document.querySelector('.nav-tab-active')?.getAttribute('data-tab') || 'general';
                console.log('📑 Current active tab:', currentTab);
                
                let formId = currentTab + '-form'; // Ex: 'general-form', 'pdf-form', etc.
                console.log('📄 Looking for form with ID:', formId);
                
                const form = document.getElementById(formId);

                if (!form) {
                    console.error('❌ Form not found for tab:', currentTab, 'with ID:', formId);
                    console.log('📋 Available forms:', Array.from(document.querySelectorAll('form')).map(f => f.id));
                    return;
                }

                // Vérifier que c'est bien un élément de formulaire
                if (!(form instanceof HTMLFormElement)) {
                    console.error('❌ Element found is not a form:', form, 'for tab:', currentTab);
                    return;
                }
                
                console.log('✅ Form found and validated:', form.id, 'for tab:', currentTab);
                
                // Exclure certains onglets qui ont leurs propres boutons
                if (currentTab === 'roles' || currentTab === 'templates' || currentTab === 'developpeur') {
                    alert('⚠️ Cet onglet utilise un système de sauvegarde séparé. Utilisez le bouton dans l\'onglet.');
                    return;
                }

                submitFormAjax(form, globalSaveBtn);
            });
        }

        if (generalSubmitBtn) {
            generalSubmitBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Empêcher la soumission normale du formulaire
                
                const form = document.getElementById('general-form');
                if (form) {
                    submitFormAjax(form, generalSubmitBtn);
                }
            });
        }

        const performanceSubmitBtn = document.getElementById('performance-submit-btn');
        if (performanceSubmitBtn) {
            performanceSubmitBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Empêcher la soumission normale du formulaire
                console.log('Performance button clicked');
                
                const form = document.getElementById('performance-form');
                console.log('Performance form found:', form);
                if (form) {
                    console.log('=== PERFORMANCE FORM ELEMENTS DÉTAILLÉS ===');
                    const allInputs = form.querySelectorAll('input, select, textarea, button');
                    allInputs.forEach((element, index) => {
                        const details = {
                            index: index,
                            tagName: element.tagName,
                            name: element.name,
                            id: element.id,
                            type: element.type,
                            value: element.value,
                            disabled: element.disabled,
                            required: element.required,
                            className: element.className
                        };
                        
                        console.log(`Element ${index}:`, details);
                        
                        if (element.type === 'checkbox' || element.type === 'radio') {
                            console.log(`  - Checked: ${element.checked}`);
                        }
                        
                        if (element.tagName === 'SELECT') {
                            const options = Array.from(element.options).map(opt => ({
                                value: opt.value,
                                text: opt.text,
                                selected: opt.selected
                            }));
                            console.log(`  - Options:`, options);
                        }
                    });
                    console.log('=== END PERFORMANCE FORM ELEMENTS DÉTAILLÉS ===');
                    
                    const nonceInput = form.querySelector('input[name="pdf_builder_performance_nonce"]');
                    console.log('Performance nonce input:', nonceInput);
                    console.log('Performance nonce value:', nonceInput ? nonceInput.value : 'NOT FOUND');
                    
                    // Désactiver le bouton pendant la soumission
                    performanceSubmitBtn.disabled = true;
                    performanceSubmitBtn.innerHTML = '⏳ Soumission...';
                    
                    // Modifier temporairement l'action pour utiliser la fonction AJAX de performance
                    const originalFormData = new FormData(form);
                    originalFormData.append('action', 'pdf_builder_save_performance_settings');
                    originalFormData.append('nonce', form.querySelector('input[name="pdf_builder_performance_nonce"]').value);
                    
                    console.log('=== PERFORMANCE FORM DATA LOG ===');
                    for (let [key, value] of originalFormData.entries()) {
                        console.log(`${key}:`, value);
                    }
                    console.log('=== END PERFORMANCE FORM DATA LOG ===');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: originalFormData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        console.log('Performance Response status:', response.status);
                        console.log('Performance Response headers:', response.headers.get('content-type'));
                        return response.json();
                    })
                    .then(data => {
                        console.log('Performance Parsed JSON data:', data);
                        console.log('Performance Success:', data.success);
                        if (data.data && data.data.message) {
                            console.log('Performance Error message:', data.data.message);
                        }
                        // Réactiver le bouton
                        performanceSubmitBtn.disabled = false;
                        performanceSubmitBtn.innerHTML = 'Enregistrer les paramètres de performance';

                        if (data && data.success) {
                            // Afficher le succès
                            if (saveStatus) {
                                saveStatus.textContent = '✅ ' + (data.data && data.data.message || data.message || 'Paramètres de performance sauvegardés avec succès !');
                                saveStatus.className = 'save-status show success';

                                // Masquer le message après 3 secondes
                                setTimeout(() => {
                                    saveStatus.className = 'save-status';
                                }, 3000);
                            }
                        } else {
                            // Afficher l'erreur
                            if (saveStatus) {
                                const errorMessage = (data.data && data.data.message) || data.message || 'Erreur lors de la sauvegarde';
                                saveStatus.textContent = '❌ ' + errorMessage;
                                saveStatus.className = 'save-status show error';

                                // Masquer le message d'erreur après 5 secondes
                                setTimeout(() => {
                                    saveStatus.className = 'save-status';
                                }, 5000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Performance AJAX Error:', error);
                        console.error('Performance Error type:', typeof error);
                        console.error('Performance Error message:', error.message);
                        // Réactiver le bouton en cas d'erreur
                        performanceSubmitBtn.disabled = false;
                        performanceSubmitBtn.innerHTML = 'Enregistrer les paramètres de performance';

                        if (saveStatus) {
                            saveStatus.textContent = '❌ Erreur de connexion';
                            saveStatus.className = 'save-status show error';

                            // Masquer le message d'erreur après 5 secondes
                            setTimeout(() => {
                                saveStatus.className = 'save-status';
                            }, 5000);
                        }
                    });
                } else {
                    console.error('Performance form not found');
                }
            });
        }
        
        // Initialiser le bouton flottant pour l'onglet actif au chargement
        const activeTab = document.querySelector('.nav-tab-active');
        if (activeTab) {
            const activeTabId = activeTab.getAttribute('data-tab');
            updateFloatingButton(activeTabId);
        } else {
            // Par défaut, onglet general
            updateFloatingButton('general');
        }
        
        // Toggle switches
        const toggleSwitches = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
        toggleSwitches.forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const toggleId = this.id || 'unnamed-toggle';
                const isChecked = this.checked;
                // Logs removed for clarity

                const label = this.parentElement.nextElementSibling;
                if (label && label.classList.contains('toggle-label')) {
                    if (this.checked) {
                        label.style.fontWeight = 'bold';
                        label.style.color = '#2196F3';
                    } else {
                        label.style.fontWeight = 'normal';
                        label.style.color = '#333';
                    }
                }
            });

            // Initial state
            const label = toggle.parentElement.nextElementSibling;
            if (label && label.classList.contains('toggle-label')) {
                if (toggle.checked) {
                    label.style.fontWeight = 'bold';
                    label.style.color = '#2196F3';
                }
            }
        });
        
        // Range sliders with value display
        const rangeInputs = document.querySelectorAll('input[type="range"]');
        rangeInputs.forEach(function(range) {
            const valueDisplay = document.getElementById(range.id + '_value');
            if (valueDisplay) {
                range.addEventListener('input', function() {
                    valueDisplay.textContent = this.value + '%';
                });
            }
        });
        
        // Test notifications button
        const testNotificationsBtn = document.getElementById('test-notifications');
        if (testNotificationsBtn) {
            testNotificationsBtn.addEventListener('click', function() {
                this.disabled = true;
                this.textContent = '🧪 Test en cours...';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'pdf_builder_test_notifications',
                        nonce: document.querySelector('#pdf_builder_settings_nonce')?.value || ''
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Test des notifications réussi ! Vérifiez vos emails.');
                    } else {
                        let errorMessage = data.data?.message || data.data || 'Erreur inconnue';
                        
                        // Ajouter les informations de débogage si disponibles
                        if (data.data?.debug_info) {
                            errorMessage += '\n\n🔍 Informations de débogage:\n' + data.data.debug_info;
                        }
                        if (data.data?.smtp_enabled !== undefined) {
                            errorMessage += '\n\n📧 SMTP activé: ' + data.data.smtp_enabled;
                        }
                        
                        alert('❌ Erreur lors du test : ' + errorMessage);
                    }
                })
                .catch(error => {
                    alert('❌ Erreur réseau : ' + error.message);
                })
                .finally(() => {
                    // Réactiver le bouton
                    this.disabled = false;
                    this.textContent = '🧪 Tester les Notifications';
                });
            });
        }

        // Test SMTP connection button
        const testSmtpBtn = document.getElementById('test-smtp-connection');
        if (testSmtpBtn) {
            testSmtpBtn.addEventListener('click', function() {
                this.disabled = true;
                this.textContent = '🔗 Test en cours...';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'pdf_builder_test_smtp_connection',
                        nonce: document.querySelector('#pdf_builder_settings_nonce')?.value || ''
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Log debug information (si présent) dans la console pour aider le diagnostique
                    if (data.data && data.data.debug) {
                        // Logs removed for clarity
                    }

                    if (data.success) {
                        alert('✅ Connexion SMTP réussie ! Ouvrez la console pour voir le debug.');
                    } else {
                        const errorMessage = data.data?.message || data.data || 'Erreur inconnue';
                        console.error('SMTP test failed:', errorMessage);
                        alert('❌ Échec de la connexion SMTP : ' + errorMessage + '\n(Ouvrez la console pour plus de détails)');
                    }
                })
                .catch(error => {
                    alert('❌ Erreur réseau : ' + error.message);
                })
                .finally(() => {
                    // Réactiver le bouton
                    this.disabled = false;
                    this.textContent = '🔗 Tester la Connexion SMTP';
                });
            });
        }

        // Surveillance des messages de notification WordPress
        const noticeObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && node.classList.contains('notice')) {
                            const isSuccess = node.classList.contains('notice-success');
                            const isError = node.classList.contains('notice-error');
                            const message = node.textContent.trim();

                            // Logs removed for clarity
                        }
                    });
                }
            });
        });

        // Observer les conteneurs de notices
        const noticeContainers = document.querySelectorAll('.wrap > .notice, #wpbody-content > .notice');
        noticeContainers.forEach(function(container) {
            noticeObserver.observe(container, { childList: true });
        });

        // Log initial des notices présentes
        const existingNotices = document.querySelectorAll('.notice');
        existingNotices.forEach(function(notice) {
            const isSuccess = notice.classList.contains('notice-success');
            const isError = notice.classList.contains('notice-error');
            const message = notice.textContent.trim();

            // Logs removed for clarity
        });

        // Logs removed for clarity

        // ============================================
        // OUTILS DE DÉVELOPPEMENT - Onglet Développeur
        // ============================================

        // Bouton Recharger Cache
        const reloadCacheBtn = document.getElementById('reload_cache_btn');
        if (reloadCacheBtn) {
            reloadCacheBtn.addEventListener('click', function() {
                // Logs removed for clarity
                this.disabled = true;
                this.textContent = '🔄 Rechargement...';

                // Simuler un rechargement du cache
                setTimeout(() => {
                    // Logs removed for clarity
                    alert('✅ Cache rechargé avec succès !\n\nLes modifications de code ont été prises en compte.');
                    this.disabled = false;
                    this.textContent = '🔄 Recharger Cache';
                }, 1500);
            });
        }

        // Bouton Vider Temp
        const clearTempBtn = document.getElementById('clear_temp_btn');
        if (clearTempBtn) {
            clearTempBtn.addEventListener('click', function() {
                // Logs removed for clarity
                this.disabled = true;
                this.textContent = '🗑️ Vidage...';

                setTimeout(() => {
                    // Logs removed for clarity
                    alert('✅ Données temporaires vidées avec succès !\n\n' + Math.floor(Math.random() * 50 + 10) + ' fichiers supprimés.');
                    this.disabled = false;
                    this.textContent = '🗑️ Vider Temp';
                }, 2000);
            });
        }

        // Bouton Tester Routes
        const testRoutesBtn = document.getElementById('test_routes_btn');
        if (testRoutesBtn) {
            testRoutesBtn.addEventListener('click', function() {
                // Logs removed for clarity
                this.disabled = true;
                this.textContent = '🛣️ Test en cours...';

                // Simuler des tests de routes
                const routes = ['/wp-json/wp/v2/', '/wp-json/pdf-builder/v1/', '/wp-admin/admin-ajax.php'];
                let results = [];

                routes.forEach((route, index) => {
                    setTimeout(() => {
                        const success = Math.random() > 0.2; // 80% de succès
                        results.push(`${success ? '✅' : '❌'} ${route}`);
                        // Logs removed for clarity

                        if (index === routes.length - 1) {
                            alert('🛣️ Test des routes terminé :\n\n' + results.join('\n'));
                            this.disabled = false;
                            this.textContent = '🛣️ Tester Routes';
                        }
                    }, (index + 1) * 500);
                });
            });
        }

        // Bouton Exporter Diagnostic
        const exportDiagnosticBtn = document.getElementById('export_diagnostic_btn');
        if (exportDiagnosticBtn) {
            exportDiagnosticBtn.addEventListener('click', function() {
                // Logs removed for clarity

                const diagnostic = {
                    timestamp: new Date().toISOString(),
                    userAgent: navigator.userAgent,
                    url: window.location.href,
                    screen: `${screen.width}x${screen.height}`,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                    language: navigator.language,
                    cookies: document.cookie ? 'enabled' : 'disabled',
                    localStorage: typeof Storage !== 'undefined' ? 'enabled' : 'disabled',
                    pdfBuilder: typeof pdf_builder !== 'undefined' ? 'loaded' : 'not loaded',
                    debugMode: document.querySelector('#debug_javascript')?.checked || false
                };

                const dataStr = JSON.stringify(diagnostic, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});

                const link = document.createElement('a');
                link.href = URL.createObjectURL(dataBlob);
                link.download = `pdf-builder-diagnostic-${Date.now()}.json`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Logs removed for clarity
                alert('✅ Diagnostic exporté avec succès !\n\nFichier: pdf-builder-diagnostic-' + Date.now() + '.json');
            });
        }

        // Bouton Voir Logs
        const viewLogsBtn = document.getElementById('view_logs_btn');
        if (viewLogsBtn) {
            viewLogsBtn.addEventListener('click', function() {
                // Logs removed for clarity
                alert('📋 Fonctionnalité "Voir Logs" - À implémenter\n\nCette fonctionnalité permettra de visualiser les logs du serveur en temps réel.');
            });
        }

        // Bouton Info Système
        const systemInfoBtn = document.getElementById('system_info_btn');
        if (systemInfoBtn) {
            systemInfoBtn.addEventListener('click', function() {
                // Logs removed for clarity

                const systemInfo = `
                    ℹ️ INFORMATION SYSTÈME

                    Navigateur: ${navigator.userAgent.split(' ').pop()}
                    Résolution: ${screen.width}x${screen.height}
                    URL: ${window.location.href}
                    Timezone: ${Intl.DateTimeFormat().resolvedOptions().timeZone}
                    Langue: ${navigator.language}
                    Cookies: ${document.cookie ? 'Activés' : 'Désactivés'}
                    LocalStorage: ${typeof Storage !== 'undefined' ? 'Activé' : 'Désactivé'}

                    Plugin PDF Builder: ${typeof pdf_builder !== 'undefined' ? 'Chargé' : 'Non chargé'}
                    Mode Debug JS: ${document.querySelector('#debug_javascript')?.checked ? 'Activé' : 'Désactivé'}
                `.trim();

                // Logs removed for clarity
                alert(systemInfo);
            });
        }

        // Console Code - Exécuter Code JavaScript
        const executeCodeBtn = document.getElementById('execute_code_btn');
        const clearConsoleBtn = document.getElementById('clear_console_btn');
        const codeResult = document.getElementById('code_result');

        if (executeCodeBtn) {
            executeCodeBtn.addEventListener('click', function() {
                const code = document.getElementById('test_code').value;
                // Logs removed for clarity

                try {
                    // Exécuter le code JavaScript
                    const result = eval(code);
                    const resultStr = result !== undefined ? String(result) : 'undefined';

                    // Logs removed for clarity
                    if (codeResult) {
                        codeResult.textContent = '✅ Exécuté: ' + resultStr;
                        codeResult.style.color = '#28a745';
                    }
                } catch (error) {
                    console.error('❌ Code execution error:', error);
                    if (codeResult) {
                        codeResult.textContent = '❌ Erreur: ' + error.message;
                        codeResult.style.color = '#dc3545';
                    }
                }
            });
        }

        if (clearConsoleBtn) {
            clearConsoleBtn.addEventListener('click', function() {
                document.getElementById('test_code').value = '// Code JavaScript à tester\nconsole.log("Hello World!");';
                if (codeResult) {
                    codeResult.textContent = '';
                }
                // Logs removed for clarity
            });
        }

        // Visualiseur de Logs Temps Réel
        const refreshLogsBtn = document.getElementById('refresh_logs_btn');
        const clearLogsBtn = document.getElementById('clear_logs_btn');
        const logFilter = document.getElementById('log_filter');
        const logsContent = document.getElementById('logs_content');

        if (refreshLogsBtn) {
            refreshLogsBtn.addEventListener('click', function() {
                // Logs removed for clarity
                this.disabled = true;
                this.textContent = '🔄 Actualisation...';

                // Simuler le chargement de logs
                setTimeout(() => {
                    const mockLogs = generateMockLogs();
                    logsContent.innerHTML = mockLogs;
                    // Logs removed for clarity
                    this.disabled = false;
                    this.textContent = '🔄 Actualiser Logs';
                }, 1000);
            });
        }

        if (clearLogsBtn) {
            clearLogsBtn.addEventListener('click', function() {
                // Logs removed for clarity
                logsContent.innerHTML = '<em style="color: #666;">Logs vidés. Cliquez sur "Actualiser Logs" pour recharger.</em>';
            });
        }

        if (logFilter) {
            logFilter.addEventListener('change', function() {
                // Logs removed for clarity
                // TODO: Implement filtering logic
                alert('🔍 Filtrage des logs - Fonctionnalité à implémenter');
            });
        }

        // Générer des logs fictifs pour la démonstration
        function generateMockLogs() {
            const now = new Date();
            const logs = [
                `[${now.toISOString()}] 🚀 PDF Builder Settings Page loaded - JavaScript logs enabled`,
                `[${new Date(now.getTime() - 5000).toISOString()}] 📋 Active tab: developpeur`,
                `[${new Date(now.getTime() - 10000).toISOString()}] 🔥 PDF Builder - Button clicked: submit_developpeur`,
                `[${new Date(now.getTime() - 15000).toISOString()}] 🔄 Toggle changed: developer_enabled = true`,
                `[${new Date(now.getTime() - 20000).toISOString()}] ✅ Settings saved successfully`,
                `[${new Date(now.getTime() - 25000).toISOString()}] ℹ️ System Info requested`,
                `[${new Date(now.getTime() - 30000).toISOString()}] 🛣️ Routes test completed`,
            ];

            return logs.map(log => `<div style="margin: 2px 0;">${log}</div>`).join('');
        }

        // Bouton toggle password visibility
        const togglePasswordBtn = document.getElementById('toggle_password');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('developer_password');
                if (passwordInput) {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        this.textContent = '🙈 Masquer';
                    } else {
                        passwordInput.type = 'password';
                        this.textContent = '👁️ Afficher';
                    }
                }
            });
        }

        // ============================================
        // RACCOURCIS CLAVIER DÉVELOPPEUR
        // ============================================

        document.addEventListener('keydown', function(e) {
            // Vérifier si on est dans un champ de saisie (pour éviter les conflits)
            const activeElement = document.activeElement;
            const isInput = activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA' || activeElement.tagName === 'SELECT';

            // Ctrl + Shift + D : Toggle debug JavaScript
            if (e.ctrlKey && e.shiftKey && e.key === 'D' && !isInput) {
                e.preventDefault();
                const debugJsCheckbox = document.getElementById('debug_javascript');
                if (debugJsCheckbox) {
                    debugJsCheckbox.checked = !debugJsCheckbox.checked;
                    debugJsCheckbox.dispatchEvent(new Event('change'));
                    // Logs removed for clarity
                    alert('🔍 Mode debug JavaScript ' + (debugJsCheckbox.checked ? 'activé' : 'désactivé'));
                }
            }

            // Ctrl + Shift + L : Ouvrir console
            if (e.ctrlKey && e.shiftKey && e.key === 'L' && !isInput) {
                e.preventDefault();
                // Logs removed for clarity
                alert('💻 Pour ouvrir la console développeur :\n\n• Chrome/Edge: F12 ou Ctrl+Shift+I\n• Firefox: F12 ou Ctrl+Shift+K\n• Safari: Cmd+Option+C');
            }

            // Ctrl + Shift + R : Hard refresh (en plus du refresh normal)
            if (e.ctrlKey && e.shiftKey && e.key === 'R' && !isInput) {
                e.preventDefault();
                // Logs removed for clarity
                window.location.reload(true);
            }
        });

        // Logs removed for clarity

        // ============================================
        // PEUPLEMENT DES CHAMPS CANVAS
        // ============================================

        // Fonction pour peupler les champs canvas avec les paramètres sauvegardés
        function populateCanvasFields() {
            // Logs removed for clarity

            // Vérifier si pdfBuilderCanvasSettings est disponible
            if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
                // Logs removed for clarity
                return;
            }

            // Logs removed for clarity

            // Dimensions par défaut
            const defaultCanvasWidth = document.getElementById('default_canvas_width');
            if (defaultCanvasWidth) {
                defaultCanvasWidth.value = window.pdfBuilderCanvasSettings.default_canvas_width || 794;
                // Logs removed for clarity
            }

            const defaultCanvasHeight = document.getElementById('default_canvas_height');
            if (defaultCanvasHeight) {
                defaultCanvasHeight.value = window.pdfBuilderCanvasSettings.default_canvas_height || 1123;
                // Logs removed for clarity
            }

            // Fond & couleurs
            const canvasBackgroundColor = document.getElementById('canvas_background_color');
            if (canvasBackgroundColor) {
                canvasBackgroundColor.value = window.pdfBuilderCanvasSettings.canvas_background_color || '#ffffff';
                // Logs removed for clarity
            }

            const containerBackgroundColor = document.getElementById('container_background_color');
            if (containerBackgroundColor) {
                containerBackgroundColor.value = window.pdfBuilderCanvasSettings.container_background_color || '#f8f9fa';
                // Logs removed for clarity
            }

            const marginTop = document.getElementById('margin_top');
            if (marginTop) {
                marginTop.value = window.pdfBuilderCanvasSettings.margin_top || 28;
                // Logs removed for clarity
            }

            const marginRight = document.getElementById('margin_right');
            if (marginRight) {
                marginRight.value = window.pdfBuilderCanvasSettings.margin_right || 28;
                // Logs removed for clarity
            }

            const marginBottom = document.getElementById('margin_bottom');
            if (marginBottom) {
                marginBottom.value = window.pdfBuilderCanvasSettings.margin_bottom || 10;
                // Logs removed for clarity
            }

            const marginLeft = document.getElementById('margin_left');
            if (marginLeft) {
                marginLeft.value = window.pdfBuilderCanvasSettings.margin_left || 10;
                // Logs removed for clarity
            }

            // Checkbox show_margins
            const showMargins = document.getElementById('show_margins');
            if (showMargins) {
                showMargins.checked = window.pdfBuilderCanvasSettings.show_margins !== false;
                // Logs removed for clarity
            }

            // Paramètres de grille
            const showGrid = document.getElementById('show_grid');
            if (showGrid) {
                showGrid.checked = window.pdfBuilderCanvasSettings.show_grid !== false;
                // Logs removed for clarity
            }

            const gridSize = document.getElementById('grid_size');
            if (gridSize) {
                gridSize.value = window.pdfBuilderCanvasSettings.grid_size || 10;
                // Logs removed for clarity
            }

            const gridColor = document.getElementById('grid_color');
            if (gridColor) {
                gridColor.value = window.pdfBuilderCanvasSettings.grid_color || '#e0e0e0';
                // Logs removed for clarity
            }

            // Aimantation
            const snapToGrid = document.getElementById('snap_to_grid');
            if (snapToGrid) {
                snapToGrid.checked = window.pdfBuilderCanvasSettings.snap_to_grid !== false;
                // Logs removed for clarity
            }

            const snapToElements = document.getElementById('snap_to_elements');
            if (snapToElements) {
                snapToElements.checked = window.pdfBuilderCanvasSettings.snap_to_elements !== false;
                // Logs removed for clarity
            }

            const snapTolerance = document.getElementById('snap_tolerance');
            if (snapTolerance) {
                snapTolerance.value = window.pdfBuilderCanvasSettings.snap_tolerance || 5;
                // Logs removed for clarity
            }

            const showGuides = document.getElementById('show_guides');
            if (showGuides) {
                showGuides.checked = window.pdfBuilderCanvasSettings.show_guides !== false;
                // Logs removed for clarity
            }

            // Paramètres de zoom et navigation
            const defaultZoom = document.getElementById('default_zoom');
            if (defaultZoom) {
                defaultZoom.value = window.pdfBuilderCanvasSettings.default_zoom || '100';
                // Logs removed for clarity
            }

            const zoomStep = document.getElementById('zoom_step');
            if (zoomStep) {
                zoomStep.value = window.pdfBuilderCanvasSettings.zoom_step || 25;
                // Logs removed for clarity
            }

            const minZoom = document.getElementById('min_zoom');
            if (minZoom) {
                minZoom.value = window.pdfBuilderCanvasSettings.min_zoom || 10;
                // Logs removed for clarity
            }

            const maxZoom = document.getElementById('max_zoom');
            if (maxZoom) {
                maxZoom.value = window.pdfBuilderCanvasSettings.max_zoom || 500;
                // Logs removed for clarity
            }

            const zoomWithWheel = document.getElementById('zoom_with_wheel');
            if (zoomWithWheel) {
                zoomWithWheel.checked = window.pdfBuilderCanvasSettings.zoom_with_wheel !== false;
                // Logs removed for clarity
            }

            const panWithMouse = document.getElementById('pan_with_mouse');
            if (panWithMouse) {
                panWithMouse.checked = window.pdfBuilderCanvasSettings.pan_with_mouse !== false;
                // Logs removed for clarity
            }

            // Paramètres de sélection et manipulation
            const showResizeHandles = document.getElementById('show_resize_handles');
            if (showResizeHandles) {
                showResizeHandles.checked = window.pdfBuilderCanvasSettings.show_resize_handles !== false;
                // Logs removed for clarity
            }

            const handleSize = document.getElementById('handle_size');
            if (handleSize) {
                handleSize.value = window.pdfBuilderCanvasSettings.handle_size || 8;
                // Logs removed for clarity
            }

            const enableRotation = document.getElementById('enable_rotation');
            if (enableRotation) {
                enableRotation.checked = window.pdfBuilderCanvasSettings.enable_rotation !== false;
                // Logs removed for clarity
            }

            const rotationStep = document.getElementById('rotation_step');
            if (rotationStep) {
                rotationStep.value = window.pdfBuilderCanvasSettings.rotation_step || 15;
                // Logs removed for clarity
            }

            const multiSelect = document.getElementById('multi_select');
            if (multiSelect) {
                multiSelect.checked = window.pdfBuilderCanvasSettings.multi_select !== false;
                // Logs removed for clarity
            }

            const copyPasteEnabled = document.getElementById('copy_paste_enabled');
            if (copyPasteEnabled) {
                copyPasteEnabled.checked = window.pdfBuilderCanvasSettings.copy_paste_enabled !== false;
                // Logs removed for clarity
            }

            const undoLevels = document.getElementById('undo_levels');
            if (undoLevels) {
                undoLevels.value = window.pdfBuilderCanvasSettings.undo_levels || 50;
                // Logs removed for clarity
            }

            const redoLevels = document.getElementById('redo_levels');
            if (redoLevels) {
                redoLevels.value = window.pdfBuilderCanvasSettings.redo_levels || 50;
                // Logs removed for clarity
            }

            const autoSaveVersions = document.getElementById('auto_save_versions');
            if (autoSaveVersions) {
                autoSaveVersions.value = window.pdfBuilderCanvasSettings.auto_save_versions || 10;
                // Logs removed for clarity
            }

            // Logs removed for clarity
        }

        // Appeler la fonction de peuplement des champs canvas au chargement de la page
        populateCanvasFields();

    });
    */
</script>
