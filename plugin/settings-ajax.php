<?php
/**
 * AJAX Handlers for PDF Builder Settings
 *
 * @package PDF_Builder_Pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AJAX handlers for settings
 */
function pdf_builder_register_ajax_handlers() {
    // Canvas settings save handler
    add_action('wp_ajax_pdf_builder_save_canvas_settings', 'pdf_builder_save_canvas_settings_handler');

    // Cache settings save handler
    add_action('wp_ajax_pdf_builder_save_cache_settings', 'pdf_builder_save_cache_settings_handler');

    // Clear cache handler (if not already defined)
    if (!has_action('wp_ajax_pdf_builder_clear_cache')) {
        add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_handler');
    }
}
add_action('init', 'pdf_builder_register_ajax_handlers');

/**
 * AJAX handler for saving canvas settings
 */
function pdf_builder_save_canvas_settings_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Get form data
        $canvas_max_size = intval($_POST['canvas_max_size'] ?? 2048);
        $canvas_dpi = intval($_POST['canvas_dpi'] ?? 300);
        $canvas_format = sanitize_text_field($_POST['canvas_format'] ?? 'PNG');
        $canvas_quality = intval($_POST['canvas_quality'] ?? 90);

        // Validate values
        $canvas_max_size = max(512, min(4096, $canvas_max_size));
        $canvas_dpi = max(72, min(600, $canvas_dpi));
        $canvas_quality = max(10, min(100, $canvas_quality));

        // Save settings
        update_option('pdf_builder_canvas_max_size', $canvas_max_size);
        update_option('pdf_builder_canvas_dpi', $canvas_dpi);
        update_option('pdf_builder_canvas_format', $canvas_format);
        update_option('pdf_builder_canvas_quality', $canvas_quality);

        wp_send_json_success(array(
            'message' => 'Paramètres canvas sauvegardés avec succès',
            'data' => array(
                'canvas_max_size' => $canvas_max_size,
                'canvas_dpi' => $canvas_dpi,
                'canvas_format' => $canvas_format,
                'canvas_quality' => $canvas_quality
            )
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for saving cache settings
 */
function pdf_builder_save_cache_settings_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Get form data
        $cache_enabled = !empty($_POST['cache_enabled']) ? '1' : '0';
        $cache_ttl = intval($_POST['cache_ttl'] ?? 3600);
        $cache_compression = !empty($_POST['cache_compression']) ? '1' : '0';
        $cache_auto_cleanup = !empty($_POST['cache_auto_cleanup']) ? '1' : '0';
        $cache_max_size = intval($_POST['cache_max_size'] ?? 100);

        // Validate values
        $cache_ttl = max(300, min(86400, $cache_ttl)); // 5 min to 24 hours
        $cache_max_size = max(10, min(1000, $cache_max_size)); // 10MB to 1GB

        // Save settings
        update_option('pdf_builder_cache_enabled', $cache_enabled);
        update_option('pdf_builder_cache_ttl', $cache_ttl);
        update_option('pdf_builder_cache_compression', $cache_compression);
        update_option('pdf_builder_cache_auto_cleanup', $cache_auto_cleanup);
        update_option('pdf_builder_cache_max_size', $cache_max_size);

        wp_send_json_success(array(
            'message' => 'Paramètres cache sauvegardés avec succès',
            'data' => array(
                'cache_enabled' => $cache_enabled,
                'cache_ttl' => $cache_ttl,
                'cache_compression' => $cache_compression,
                'cache_auto_cleanup' => $cache_auto_cleanup,
                'cache_max_size' => $cache_max_size
            )
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for clearing cache
 */
function pdf_builder_clear_cache_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Clear WordPress cache
        wp_cache_flush();

        // Clear plugin transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        // Clear file cache if exists
        $cache_dirs = array(
            WP_CONTENT_DIR . '/cache/pdf-builder',
            WP_CONTENT_DIR . '/cache/pdf-builder-preview'
        );

        $cleared_files = 0;
        foreach ($cache_dirs as $cache_dir) {
            if (file_exists($cache_dir) && is_dir($cache_dir)) {
                $files = glob($cache_dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && unlink($file)) {
                        $cleared_files++;
                    }
                }
            }
        }

        wp_send_json_success(array(
            'message' => 'Cache vidé avec succès',
            'cleared_files' => $cleared_files
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du nettoyage du cache: ' . $e->getMessage());
    }
}