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

if (!defined('PDF_BUILDER_PRO_VERSION')) {
    define('PDF_BUILDER_PRO_VERSION', '1.0.0');
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
    define('PDF_BUILDER_NONCE_LIFETIME', 24 * 60 * 60); // 24 heures
}

if (!defined('PDF_BUILDER_SESSION_TIMEOUT')) {
    define('PDF_BUILDER_SESSION_TIMEOUT', 30 * 60); // 30 minutes
}

if (!defined('PDF_BUILDER_CACHE_LIFETIME')) {
    define('PDF_BUILDER_CACHE_LIFETIME', 60 * 60); // 1 heure
}

// Limites de taux (requêtes par minute)
if (!defined('PDF_BUILDER_RATE_LIMIT_CANVAS')) {
    define('PDF_BUILDER_RATE_LIMIT_CANVAS', 60); // 60 actions canvas/minute
}

if (!defined('PDF_BUILDER_RATE_LIMIT_GENERATE')) {
    define('PDF_BUILDER_RATE_LIMIT_GENERATE', 10); // 10 générations/minute
}

// Limites de données
if (!defined('PDF_BUILDER_MAX_CANVAS_ELEMENTS')) {
    define('PDF_BUILDER_MAX_CANVAS_ELEMENTS', 100); // Maximum 100 éléments par canvas
}

if (!defined('PDF_BUILDER_MAX_ELEMENT_SIZE')) {
    define('PDF_BUILDER_MAX_ELEMENT_SIZE', 50 * 1024 * 1024); // 50MB max par élément
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
    define('PDF_BUILDER_SECURITY_LOG_LEVEL', 'warning'); // error, warning, info
}

// Meta keys sécurisées pour le stockage
if (!defined('PDF_BUILDER_CANVAS_META_KEY')) {
    define('PDF_BUILDER_CANVAS_META_KEY', '_pdf_builder_canvas_data');
}