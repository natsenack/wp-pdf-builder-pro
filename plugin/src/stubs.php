<?php
/**
 * Stubs for WordPress functions to satisfy Intelephense
 */

// Dompdf stubs
namespace Dompdf {
    class Dompdf {
        public function __construct($options = null) {}
        public function loadHtml($html) {}
        public function setPaper($size, $orientation) {}
        public function render() {}
        public function output() { return ''; }
        public function set_option($key, $value) {}
    }
    class Options {
        public function __construct() {}
    }
}

namespace {
    // Constants
    define('DOING_AJAX', false);
    define('REST_REQUEST', false);
    define('WP_DEBUG', false);
    define('ARRAY_A', 2);
    define('ARRAY_N', 3);
    define('ABSPATH', '/path/to/wordpress/');
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

    // Functions
    function wp_kses_post($content) { return $content; }
    function wp_verify_nonce($nonce, $action) { return true; }
    function current_user_can($capability) { return true; }
    function sanitize_text_field($str) { return $str; }
    function get_option($option, $default = false) { return $default; }
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {}
    function update_option($option, $value) { return true; }
    function delete_option($option) { return true; }
    function is_ssl() { return false; }
    function wp_safe_redirect($location, $status = 302) {}
    function register_setting($option_group, $option_name, $args = array()) {}
    function wp_localize_script($handle, $object_name, $l10n) { return true; }
    function admin_url($path = '') { return $path; }
    function wp_create_nonce($action) { return 'nonce'; }
    function __($text, $domain = 'default') { return $text; }
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {}
    function is_admin() { return false; }
    function wp_doing_ajax() { return false; }
    function is_user_logged_in() { return true; }
    function wp_die($message = '') { die($message); }
    function add_option($option, $value, $deprecated = '', $autoload = 'yes') { return true; }
    function wp_send_json_error($data = null) { echo json_encode($data); exit; }
    function wp_send_json_success($data = null) { echo json_encode($data); exit; }
    function get_post($post = null, $output = 'OBJECT', $filter = 'raw') { return null; }
    function get_post_meta($post_id, $key = '', $single = false) { return $single ? '' : array(); }
    function get_theme_mod($name, $default = false) { return $default; }
    function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail') { return ''; }
    function plugin_dir_url($file) { return ''; }
    function plugin_basename($file) { return ''; }
    function deactivate_plugins($plugins, $silent = false, $network_wide = null) {}
    function get_bloginfo($show = '') { return ''; }
    function dbDelta($queries = '', $execute = true) { return []; }
    function get_current_user_id() { return 1; }
    function wp_mkdir_p($dir) { return true; }
    function current_time($type, $gmt = 0) { return time(); }
    function wp_date($format, $timestamp = null, $timezone = null) { return date($format, $timestamp ?: time()); }
    function wp_timezone_string() { return 'UTC'; }
    function maybe_unserialize($original) { return $original; }
    function size_format($bytes, $decimals = 0) { return ''; }
    function sanitize_file_name($filename) { return $filename; }
    function wp_send_json($response, $status_code = null, $options = 0) { echo json_encode($response); exit; }
    function wp_upload_dir($time = null) { return ['path' => '', 'url' => '', 'subdir' => '', 'basedir' => '', 'baseurl' => '', 'error' => false]; }
    function plugin_dir_path($file) { return ''; }
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {}
    function set_transient($transient, $value, $expiration = 0) { return true; }
    function get_transient($transient) { return false; }
    function delete_transient($transient) { return true; }
    function wp_cache_flush() { return true; }

    // Additional missing functions
    function wp_json_encode($data, $options = 0, $depth = 512) { return json_encode($data, $options, $depth); }
    function sanitize_textarea_field($str) { return $str; }
    function do_action($tag, ...$args) {}
    function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES); }
    function esc_html($text) { return htmlspecialchars($text, ENT_NOQUOTES); }
    function esc_url($url, $protocols = null, $_context = 'display') { return $url; }
    function is_email($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
    function sanitize_email($email) { return filter_var($email, FILTER_SANITIZE_EMAIL); }
    function esc_url_raw($url, $protocols = null) { return $url; }
    function sanitize_hex_color($color) { return $color; }
    function wp_cache_delete($key, $group = '') { return true; }
    function wp_cache_set($key, $data, $group = '', $expire = 0) { return true; }
    function wp_cache_get($key, $group = '', $force = false, $found = null) { return false; }
    function wp_rand($min = 0, $max = 0) { return rand($min, $max); }
    function get_temp_dir() { return sys_get_temp_dir(); }
    function wp_remote_head($url, $args = array()) { return array('response' => array('code' => 200)); }
    function is_wp_error($thing) { return $thing instanceof WP_Error; }
    function get_site_url($blog_id = null, $path = '', $scheme = null) { return 'http://example.com'; }
    function is_multisite() { return false; }

    // Stub class for WP_Error
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_message($code = '') { return ''; }
    }

    // Stub for PDF_Builder_Secure_Shell_Manager
    class PDF_Builder_Secure_Shell_Manager {
        public static function is_command_available($command) { return false; }
        public static function execute_wkhtmltopdf($html_file, $pdf_path) { return false; }
        public static function execute_node($script_path, $args) { return ''; }
        public static function is_secure_file_path($path) { return true; }
    }
}
