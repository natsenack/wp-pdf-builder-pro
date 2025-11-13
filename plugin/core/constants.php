<?php

/**
 * PDF Builder Pro Constants
 *
 * Plugin constants and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin capabilities
if (!defined('PDF_BUILDER_ADMIN_CAPABILITY')) {
    define('PDF_BUILDER_ADMIN_CAPABILITY', 'manage_options');
}

// Plugin version management
if (!defined('PDF_BUILDER_VERSION')) {
    define('PDF_BUILDER_VERSION', '1.0.1');
}

if (!defined('PDF_BUILDER_PRO_VERSION')) {
    define('PDF_BUILDER_PRO_VERSION', '1.1.0');
}

/**
 * Get the plugin version from header
 * This ensures version consistency across the plugin
 */
function pdf_builder_get_version() {
    static $version = null;

    if ($version === null) {
        if (defined('PDF_BUILDER_PLUGIN_FILE') && file_exists(PDF_BUILDER_PLUGIN_FILE)) {
            $plugin_data = get_file_data(PDF_BUILDER_PLUGIN_FILE, array('Version' => 'Version'));
            $version = $plugin_data['Version'] ?: pdf_builder_get_current_version();
        } else {
            $version = pdf_builder_get_current_version();
        }
    }

    return $version;
}

/**
 * Get the current version based on license status
 * Returns PRO version if license is active, FREE version otherwise
 */
function pdf_builder_get_current_version() {
    if (pdf_builder_is_pro_license_active()) {
        return PDF_BUILDER_PRO_VERSION;
    } else {
        return PDF_BUILDER_VERSION;
    }
}

/**
 * Check if PRO license is active
 */
function pdf_builder_is_pro_license_active() {
    // Check for license activation option
    $license_active = get_option('pdf_builder_pro_license_active', false);

    // Also check for license manager if it exists
    if (class_exists('PDF_Builder_License_Manager')) {
        $license_manager = PDF_Builder_License_Manager::get_instance();
        if (method_exists($license_manager, 'is_license_active')) {
            return $license_manager->is_license_active();
        }
    }

    return (bool) $license_active;
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

if (!defined('PDF_BUILDER_PRO_ASSETS_URL')) {
    define('PDF_BUILDER_PRO_ASSETS_URL', PDF_BUILDER_PLUGIN_URL . 'assets/');
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

if (!defined('PDF_BUILDER_PRO_UPLOADS_DIR')) {
    define('PDF_BUILDER_PRO_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/pdf-builder-pro/');
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

// ==========================================
// SÉCURITÉ - Constantes générales
// ==========================================

// Nonces pour les différents contextes
if (!defined('PDF_BUILDER_CANVAS_NONCE')) {
    define('PDF_BUILDER_CANVAS_NONCE', 'pdf_builder_canvas_nonce');
}

if (!defined('PDF_BUILDER_ORDER_ACTIONS_NONCE')) {
    define('PDF_BUILDER_ORDER_ACTIONS_NONCE', 'pdf_builder_order_actions');
}

// Timeouts de sécurité (en secondes)
if (!defined('PDF_BUILDER_NONCE_LIFETIME')) {
    define('PDF_BUILDER_NONCE_LIFETIME', 24 * 60 * 60);
// 24 heures
}

if (!defined('PDF_BUILDER_SESSION_TIMEOUT')) {
    define('PDF_BUILDER_SESSION_TIMEOUT', 30 * 60);
// 30 minutes
}

if (!defined('PDF_BUILDER_CACHE_LIFETIME')) {
    define('PDF_BUILDER_CACHE_LIFETIME', 60 * 60);
// 1 heure
}

// Limites de taux (requêtes par minute)
if (!defined('PDF_BUILDER_RATE_LIMIT_CANVAS')) {
    define('PDF_BUILDER_RATE_LIMIT_CANVAS', 60);
// 60 actions canvas/minute
}

if (!defined('PDF_BUILDER_RATE_LIMIT_GENERATE')) {
    define('PDF_BUILDER_RATE_LIMIT_GENERATE', 10);
// 10 générations/minute
}

// Limites de données
if (!defined('PDF_BUILDER_MAX_CANVAS_ELEMENTS')) {
    define('PDF_BUILDER_MAX_CANVAS_ELEMENTS', 100);
// Maximum 100 éléments par canvas
}

if (!defined('PDF_BUILDER_MAX_ELEMENT_SIZE')) {
    define('PDF_BUILDER_MAX_ELEMENT_SIZE', 50 * 1024 * 1024);
// 50MB max par élément
}

// Sanitisation et validation
if (!defined('PDF_BUILDER_ALLOWED_HTML_TAGS')) {
    define('PDF_BUILDER_ALLOWED_HTML_TAGS', 'strong,em,u,br,p,span');
}

if (!defined('PDF_BUILDER_ALLOWED_PROTOCOLS')) {
    define('PDF_BUILDER_ALLOWED_PROTOCOLS', 'http,https,data');
}

// Logging de sécurité
if (!defined('PDF_BUILDER_SECURITY_LOG_ENABLED')) {
    define('PDF_BUILDER_SECURITY_LOG_ENABLED', true);
}

if (!defined('PDF_BUILDER_SECURITY_LOG_LEVEL')) {
    define('PDF_BUILDER_SECURITY_LOG_LEVEL', 'warning');
// error, warning, info
}

// Meta keys sécurisées pour le stockage
if (!defined('PDF_BUILDER_CANVAS_META_KEY')) {
    define('PDF_BUILDER_CANVAS_META_KEY', '_pdf_builder_canvas_data');
}
