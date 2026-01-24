<?php
/**
 * Stubs for WordPress functions to satisfy Intelephense
 */

// Constants
define('DOING_AJAX', false);
define('REST_REQUEST', false);
define('WP_DEBUG', false);
define('ARRAY_A', 2);
define('ABSPATH', '/path/to/wordpress/');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

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
