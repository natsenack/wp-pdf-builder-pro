<?php
/**
 * PDF Builder Pro Constants
 *
 * Plugin constants and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
if (!defined('PDF_BUILDER_VERSION')) {
    define('PDF_BUILDER_VERSION', '1.0.0');
}

// Plugin paths
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
}

if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(PDF_BUILDER_PLUGIN_FILE));
}

if (!defined('PDF_BUILDER_PLUGIN_URL')) {
    define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url(PDF_BUILDER_PLUGIN_FILE));
}

// Core paths
if (!defined('PDF_BUILDER_CORE_DIR')) {
    define('PDF_BUILDER_CORE_DIR', PDF_BUILDER_PLUGIN_DIR . 'core/');
}

if (!defined('PDF_BUILDER_SRC_DIR')) {
    define('PDF_BUILDER_SRC_DIR', PDF_BUILDER_PLUGIN_DIR . 'src/');
}

if (!defined('PDF_BUILDER_ASSETS_DIR')) {
    define('PDF_BUILDER_ASSETS_DIR', PDF_BUILDER_PLUGIN_DIR . 'assets/');
}

if (!defined('PDF_BUILDER_ASSETS_URL')) {
    define('PDF_BUILDER_ASSETS_URL', PDF_BUILDER_PLUGIN_URL . 'assets/');
}

if (!defined('PDF_BUILDER_RESOURCES_DIR')) {
    define('PDF_BUILDER_RESOURCES_DIR', PDF_BUILDER_PLUGIN_DIR . 'resources/');
}

if (!defined('PDF_BUILDER_TEMPLATES_DIR')) {
    define('PDF_BUILDER_TEMPLATES_DIR', PDF_BUILDER_PLUGIN_DIR . 'templates/');
}

if (!defined('PDF_BUILDER_CONFIG_DIR')) {
    define('PDF_BUILDER_CONFIG_DIR', PDF_BUILDER_PLUGIN_DIR . 'config/');
}

if (!defined('PDF_BUILDER_LANGUAGES_DIR')) {
    define('PDF_BUILDER_LANGUAGES_DIR', PDF_BUILDER_PLUGIN_DIR . 'languages/');
}

// Upload paths
if (!defined('PDF_BUILDER_UPLOAD_DIR')) {
    define('PDF_BUILDER_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/pdf-builder-pro/');
}

if (!defined('PDF_BUILDER_CACHE_DIR')) {
    define('PDF_BUILDER_CACHE_DIR', PDF_BUILDER_UPLOAD_DIR . 'cache/');
}

if (!defined('PDF_BUILDER_LOGS_DIR')) {
    define('PDF_BUILDER_LOGS_DIR', PDF_BUILDER_UPLOAD_DIR . 'logs/');
}

// Database tables
if (!defined('PDF_BUILDER_TEMPLATES_TABLE')) {
    global $wpdb;
    define('PDF_BUILDER_TEMPLATES_TABLE', $wpdb->prefix . 'pdf_builder_templates');
}

if (!defined('PDF_BUILDER_SETTINGS_TABLE')) {
    global $wpdb;
    define('PDF_BUILDER_SETTINGS_TABLE', $wpdb->prefix . 'pdf_builder_settings');
}

// Capabilities
if (!defined('PDF_BUILDER_ADMIN_CAPABILITY')) {
    define('PDF_BUILDER_ADMIN_CAPABILITY', 'manage_options');
}

if (!defined('PDF_BUILDER_EDITOR_CAPABILITY')) {
    define('PDF_BUILDER_EDITOR_CAPABILITY', 'edit_pages');
}

// AJAX actions
if (!defined('PDF_BUILDER_AJAX_PREFIX')) {
    define('PDF_BUILDER_AJAX_PREFIX', 'pdf_builder_');
}