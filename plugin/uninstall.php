<?php
/**
 * PDF Builder Pro - Uninstall Script
 *
 * This file is called when the plugin is uninstalled via WordPress admin.
 * It handles complete cleanup of all plugin data.
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include WordPress functions if not already loaded
if (!function_exists('get_option')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Main uninstall function
 */
function pdf_builder_pro_uninstall() {
    global $wpdb;

    try {
        // Log uninstall start
        error_log('PDF Builder Pro: Starting uninstall process');

        // 1. Remove database tables
        pdf_builder_remove_database_tables($wpdb);

        // 2. Remove plugin options
        pdf_builder_remove_plugin_options($wpdb);

        // 3. Remove plugin directories and files
        pdf_builder_remove_plugin_directories();

        // 4. Remove scheduled events
        pdf_builder_remove_scheduled_events();

        // 5. Remove user meta (if any)
        pdf_builder_remove_user_meta($wpdb);

        // Log uninstall completion
        error_log('PDF Builder Pro: Uninstall process completed successfully');

    } catch (Exception $e) {
        error_log('PDF Builder Pro: Error during uninstall: ' . $e->getMessage());
    }
}

/**
 * Remove all plugin database tables
 */
function pdf_builder_remove_database_tables($wpdb) {
    // Tables to remove
    $tables_to_remove = array(
        $wpdb->prefix . 'pdf_builder_templates',
        $wpdb->prefix . 'pdf_builder_logs',
        $wpdb->prefix . 'pdf_builder_pdfs', // If exists
        $wpdb->prefix . 'pdf_builder_cache', // If exists
        $wpdb->prefix . 'pdf_builder_sessions', // If exists
    );

    foreach ($tables_to_remove as $table_name) {
        // Check if table exists before trying to drop it
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            $wpdb->query("DROP TABLE `$table_name`");
            error_log("PDF Builder Pro: Dropped table $table_name");
        }
    }
}

/**
 * Remove all plugin options from wp_options table
 */
function pdf_builder_remove_plugin_options($wpdb) {
    // Get all options that start with pdf_builder
    $options = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%'",
        ARRAY_A
    );

    foreach ($options as $option) {
        delete_option($option['option_name']);
        error_log("PDF Builder Pro: Removed option {$option['option_name']}");
    }

    // Also remove any transients
    $transients = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%' OR option_name LIKE '_transient_timeout_pdf_builder_%'",
        ARRAY_A
    );

    foreach ($transients as $transient) {
        delete_option($transient['option_name']);
        error_log("PDF Builder Pro: Removed transient {$transient['option_name']}");
    }
}

/**
 * Remove plugin directories and uploaded files
 */
function pdf_builder_remove_plugin_directories() {
    $upload_dir = wp_upload_dir();
    $base_upload_dir = $upload_dir['basedir'];

    // Directories to remove
    $directories_to_remove = array(
        $base_upload_dir . '/pdf-builder-backups',
        $base_upload_dir . '/pdf-builder-temp',
        $base_upload_dir . '/pdf-builder-cache',
        $base_upload_dir . '/pdf-builder-previews',
        WP_CONTENT_DIR . '/cache/pdf-builder',
        WP_CONTENT_DIR . '/cache/pdf-builder-preview',
        WP_CONTENT_DIR . '/pdf-builder-logs',
    );

    foreach ($directories_to_remove as $directory) {
        if (file_exists($directory) && is_dir($directory)) {
            pdf_builder_remove_directory_recursive($directory);
            error_log("PDF Builder Pro: Removed directory $directory");
        }
    }

    // Remove any remaining PDF files in uploads that might be related
    $pdf_files_pattern = $base_upload_dir . '/pdf-builder-*.pdf';
    $pdf_files = glob($pdf_files_pattern);
    foreach ($pdf_files as $pdf_file) {
        if (file_exists($pdf_file) && is_file($pdf_file)) {
            unlink($pdf_file);
            error_log("PDF Builder Pro: Removed PDF file $pdf_file");
        }
    }
}

/**
 * Remove scheduled events (cron jobs)
 */
function pdf_builder_remove_scheduled_events() {
    // Remove scheduled hooks
    $scheduled_hooks = array(
        'pdf_builder_daily_backup',
        'pdf_builder_cleanup_old_backups',
        'pdf_builder_weekly_maintenance',
        'pdf_builder_auto_update_check',
        'pdf_builder_license_expiration_check',
    );

    foreach ($scheduled_hooks as $hook) {
        wp_clear_scheduled_hook($hook);
        error_log("PDF Builder Pro: Cleared scheduled hook $hook");
    }
}

/**
 * Remove user meta data related to the plugin
 */
function pdf_builder_remove_user_meta($wpdb) {
    // Remove user meta that starts with pdf_builder
    $user_meta_keys = array(
        'pdf_builder_user_preferences',
        'pdf_builder_last_login',
        'pdf_builder_template_count',
        'pdf_builder_premium_status',
    );

    foreach ($user_meta_keys as $meta_key) {
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
                $meta_key
            )
        );
        error_log("PDF Builder Pro: Removed user meta $meta_key");
    }
}

/**
 * Recursively remove a directory and all its contents
 */
function pdf_builder_remove_directory_recursive($directory) {
    if (!file_exists($directory) || !is_dir($directory)) {
        return false;
    }

    $files = array_diff(scandir($directory), array('.', '..'));

    foreach ($files as $file) {
        $file_path = $directory . DIRECTORY_SEPARATOR . $file;

        if (is_dir($file_path)) {
            pdf_builder_remove_directory_recursive($file_path);
        } else {
            unlink($file_path);
        }
    }

    return rmdir($directory);
}

// Execute uninstall
pdf_builder_pro_uninstall();