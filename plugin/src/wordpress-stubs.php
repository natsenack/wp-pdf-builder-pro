<?php
/**
 * WordPress Function Stubs for PDF Builder Pro
 * Déclarations de fonctions WordPress pour éviter les erreurs de linting
 */

// Fonctions de base
if (!function_exists('add_action')) {
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('add_filter')) {
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('do_action')) {
    function do_action($tag, ...$args) {}
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args) { return $value; }
}

// Fonctions AJAX
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {}
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {}
}
if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null, $status_code = null, $options = 0) {}
}
if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = null, $options = 0) {}
}

// Fonctions de sanitisation
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {}
}
if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {}
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {}
}

// Fonctions d'options
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {}
}
if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {}
}
if (!function_exists('delete_option')) {
    function delete_option($option) {}
}

// Cache et transients
if (!function_exists('wp_cache_set')) {
    function wp_cache_set($key, $data, $group = '', $expire = 0) {}
}
if (!function_exists('wp_cache_get')) {
    function wp_cache_get($key, $group = '', $force = false, &$found = null) {}
}
if (!function_exists('wp_cache_delete')) {
    function wp_cache_delete($key, $group = '') {}
}
if (!function_exists('wp_cache_flush')) {
    function wp_cache_flush() {}
}
if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {}
}
if (!function_exists('get_transient')) {
    function get_transient($transient) {}
}
if (!function_exists('delete_transient')) {
    function delete_transient($transient) {}
}

// Fonctions de temps et date
if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {}
}

// Uploads et fichiers
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir($time = null, $create_dir = true, $refresh_cache = false) {}
}
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($target) {}
}
if (!function_exists('get_temp_dir')) {
    function get_temp_dir() {}
}

// Utilitaires
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {}
}
if (!function_exists('wp_rand')) {
    function wp_rand($min = 0, $max = 0) {}
}
if (!function_exists('size_format')) {
    function size_format($bytes, $decimals = 0) {}
}
if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = array()) {}
}

// Permissions et utilisateurs
if (!function_exists('current_user_can')) {
    function current_user_can($capability, ...$args) {}
}
if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {}
}
if (!function_exists('is_admin')) {
    function is_admin() {}
}
if (!function_exists('wp_doing_ajax')) {
    function wp_doing_ajax() {}
}

// Internationalisation
if (!function_exists('__')) {
    function __($text, $domain = 'default') {}
}
if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {}
}

// Plugins et admin
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {}
}
if (!function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {}
}
if (!function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null) {}
}
if (!function_exists('add_settings_section')) {
    function add_settings_section($id, $title, $callback, $page, $args = array()) {}
}

// Écran et interface
if (!function_exists('get_current_screen')) {
    function get_current_screen() {}
}

// Multisite
if (!function_exists('is_multisite')) {
    function is_multisite() {}
}
if (!function_exists('get_site_url')) {
    function get_site_url($blog_id = null, $path = '', $scheme = null) {}
}

// Rôles et capacités
if (!function_exists('wp_roles')) {
    function wp_roles() {}
}

// Hooks
if (!function_exists('did_action')) {
    function did_action($tag) {}
}

// Contenu
if (!function_exists('has_shortcode')) {
    function has_shortcode($content, $tag) {}
}
if (!function_exists('has_block')) {
    function has_block($block_name, $post = null) {}
}

// HTTP
if (!function_exists('wp_remote_head')) {
    function wp_remote_head($url, $args = array()) {}
}
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {}
}

// URLs
if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {}
}

// Classes stubs
if (!class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
    class PdfBuilderAdminNew {
        public static function is_premium_user() { return false; }
    }
}

// Fonctions d'administration
if (!function_exists('register_setting')) {
    function register_setting($option_group, $option_name, $args = array()) {}
}
if (!function_exists('add_settings_field')) {
    function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array()) {}
}

// Échappement HTML
if (!function_exists('esc_attr')) {
    function esc_attr($text) {}
}
if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {}
}
if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {}
}
if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {}
}

// Utilitaires
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {}
}
if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {}
}
if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {}
}
if (!function_exists('is_email')) {
    function is_email($email) {}
}
if (!function_exists('absint')) {
    function absint($maybeint) {}
}
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {}
}
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {}
}

// Constantes
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', '');
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 2);
}
if (!defined('ARRAY_N')) {
    define('ARRAY_N', 1);
}