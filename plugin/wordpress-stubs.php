<?php
/**
 * WordPress function stubs for Intelephense
 * This file contains stubs for WordPress functions to satisfy IDE analysis
 * This file is for development only and should not be deployed to production
 */

// WordPress Core Functions
if (!function_exists('wp_create_nonce')) {
    /**
     * @param string $action
     * @return string
     */
    function wp_create_nonce($action) { return ''; }
}

if (!function_exists('wp_verify_nonce')) {
    /**
     * @param string $nonce
     * @param string $action
     * @return false|int
     */
    function wp_verify_nonce($nonce, $action) { return 1; }
}

if (!function_exists('get_current_user_id')) {
    /**
     * @return int
     */
    function get_current_user_id() { return 1; }
}

if (!function_exists('get_option')) {
    /**
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    function get_option($option, $default = false) { return $default; }
}

if (!function_exists('update_option')) {
    /**
     * @param string $option
     * @param mixed $value
     * @return bool
     */
    function update_option($option, $value) { return true; }
}

if (!function_exists('add_option')) {
    /**
     * @param string $option
     * @param mixed $value
     * @param string $deprecated
     * @param string $autoload
     * @return bool
     */
    function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') { return true; }
}

if (!function_exists('delete_option')) {
    /**
     * @param string $option
     * @return bool
     */
    function delete_option($option) { return true; }
}

if (!function_exists('esc_js')) {
    /**
     * @param string $text
     * @return string
     */
    function esc_js($text) { return $text; }
}

if (!function_exists('esc_html')) {
    /**
     * @param string $text
     * @return string
     */
    function esc_html($text) { return $text; }
}

if (!function_exists('esc_url')) {
    /**
     * @param string $url
     * @param array $protocols
     * @param string $_context
     * @return string
     */
    function esc_url($url, $protocols = null, $_context = 'display') { return $url; }
}

if (!function_exists('esc_attr')) {
    /**
     * @param string $text
     * @return string
     */
    function esc_attr($text) { return $text; }
}

if (!function_exists('admin_url')) {
    /**
     * @param string $path
     * @param string $scheme
     * @return string
     */
    function admin_url($path = '', $scheme = 'admin') { return 'https://example.com/wp-admin/' . $path; }
}

if (!function_exists('_e')) {
    /**
     * @param string $text
     * @param string $domain
     * @return void
     */
    function _e($text, $domain = 'default') { echo $text; }
}

if (!function_exists('__')) {
    /**
     * @param string $text
     * @param string $domain
     * @return string
     */
    function __($text, $domain = 'default') { return $text; }
}

if (!function_exists('wp_safe_redirect')) {
    /**
     * @param string $location
     * @param int $status
     * @param string $x_redirect_by
     * @return void
     */
    function wp_safe_redirect($location, $status = 302, $x_redirect_by = 'WordPress') { return; }
}

if (!function_exists('is_ssl')) {
    /**
     * @return bool
     */
    function is_ssl() { return false; }
}

if (!function_exists('wp_enqueue_script')) {
    /**
     * @param string $handle
     * @param string $src
     * @param array $deps
     * @param string|bool|null $ver
     * @param bool $in_footer
     * @return void
     */
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) { return; }
}

if (!function_exists('wp_enqueue_style')) {
    /**
     * @param string $handle
     * @param string $src
     * @param array $deps
     * @param string|bool|null $ver
     * @param string $media
     * @return void
     */
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') { return; }
}

if (!function_exists('wp_localize_script')) {
    /**
     * @param string $handle
     * @param string $object_name
     * @param array $l10n
     * @return bool
     */
    function wp_localize_script($handle, $object_name, $l10n) { return true; }
}

if (!function_exists('wp_die')) {
    /**
     * @param string|WP_Error $message
     * @param string $title
     * @param array $args
     * @return void
     */
    function wp_die($message = '', $title = '', $args = array()) { return; }
}

if (!function_exists('wp_send_json_success')) {
    /**
     * @param mixed $data
     * @return void
     */
    function wp_send_json_success($data = null) { return; }
}

if (!function_exists('wp_send_json_error')) {
    /**
     * @param mixed $data
     * @return void
     */
    function wp_send_json_error($data = null) { return; }
}

if (!function_exists('sanitize_text_field')) {
    /**
     * @param string $str
     * @return string
     */
    function sanitize_text_field($str) { return $str; }
}

if (!function_exists('wp_kses_post')) {
    /**
     * @param string $data
     * @return string
     */
    function wp_kses_post($data) { return $data; }
}

if (!function_exists('register_setting')) {
    /**
     * @param string $option_group
     * @param string $option_name
     * @param array $args
     * @return void
     */
    function register_setting($option_group, $option_name, $args = array()) { return; }
}

if (!function_exists('is_admin')) {
    /**
     * @return bool
     */
    function is_admin() { return false; }
}

if (!function_exists('wp_doing_ajax')) {
    /**
     * @return bool
     */
    function wp_doing_ajax() { return false; }
}

if (!function_exists('is_user_logged_in')) {
    /**
     * @return bool
     */
    function is_user_logged_in() { return true; }
}

if (!function_exists('current_user_can')) {
    /**
     * @param string $capability
     * @return bool
     */
    function current_user_can($capability) { return true; }
}

if (!function_exists('get_post')) {
    /**
     * @param int|WP_Post|null $post
     * @param string $output
     * @param string $filter
     * @return WP_Post|array|null
     */
    function get_post($post = null, $output = OBJECT, $filter = 'raw') { return null; }
}

if (!function_exists('get_post_meta')) {
    /**
     * @param int $post_id
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    function get_post_meta($post_id, $key = '', $single = false) { return null; }
}

if (!function_exists('get_theme_mod')) {
    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    function get_theme_mod($name, $default = false) { return $default; }
}

if (!function_exists('wp_get_attachment_image_url')) {
    /**
     * @param int $attachment_id
     * @param string|array $size
     * @return string|false
     */
    function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail') { return ''; }
}

if (!function_exists('wp_unslash')) {
    /**
     * @param string|array $value
     * @return string|array
     */
    function wp_unslash($value) { return $value; }
}

if (!function_exists('register_setting')) {
    /**
     * @param string $option_group
     * @param string $option_name
     * @param array $args
     * @return void
     */
    function register_setting($option_group, $option_name, $args = array()) { return; }
}

if (!function_exists('add_settings_section')) {
    /**
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @param string $page
     * @param array $args
     * @return void
     */
    function add_settings_section($id, $title, $callback, $page, $args = array()) { return; }
}

if (!function_exists('add_settings_field')) {
    /**
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @param string $page
     * @param string $section
     * @param array $args
     * @return void
     */
    function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array()) { return; }
}

if (!function_exists('plugin_dir_path')) {
    /**
     * @param string $file
     * @return string
     */
    function plugin_dir_path($file) { return dirname($file) . '/'; }
}

if (!function_exists('wp_json_encode')) {
    /**
     * @param mixed $data
     * @param int $options
     * @param int $depth
     * @return string|false
     */
    function wp_json_encode($data, $options = 0, $depth = 512) { return json_encode($data, $options, $depth); }
}

if (!function_exists('current_time')) {
    /**
     * @param string $type
     * @param int|bool $gmt
     * @return string|int
     */
    function current_time($type, $gmt = 0) { return date($type === 'timestamp' ? 'U' : 'Y-m-d H:i:s'); }
}

if (!function_exists('did_action')) {
    /**
     * @param string $tag
     * @return int
     */
    function did_action($tag) { return 0; }
}

if (!function_exists('sanitize_textarea_field')) {
    /**
     * @param string $str
     * @return string
     */
    function sanitize_textarea_field($str) { return $str; }
}

if (!function_exists('sanitize_email')) {
    /**
     * @param string $email
     * @return string
     */
    function sanitize_email($email) { return $email; }
}

if (!function_exists('absint')) {
    /**
     * @param mixed $maybeint
     * @return int
     */
    function absint($maybeint) { return (int) $maybeint; }
}

if (!function_exists('esc_textarea')) {
    /**
     * @param string $text
     * @return string
     */
    function esc_textarea($text) { return $text; }
}

if (!function_exists('checked')) {
    /**
     * @param mixed $checked
     * @param mixed $current
     * @param bool $echo
     * @return string
     */
    function checked($checked, $current = true, $echo = true) { return $checked == $current ? 'checked' : ''; }
}

if (!function_exists('selected')) {
    /**
     * @param mixed $selected
     * @param mixed $current
     * @param bool $echo
     * @return string
     */
    function selected($selected, $current = true, $echo = true) { return $selected == $current ? 'selected' : ''; }
}

if (!function_exists('wp_get_current_user')) {
    /**
     * @return WP_User
     */
    function wp_get_current_user() { return null; }
}

if (!function_exists('add_settings_error')) {
    /**
     * @param string $setting
     * @param string $code
     * @param string $message
     * @param string $type
     * @return void
     */
    function add_settings_error($setting, $code, $message, $type = 'error') { return; }
}

if (!function_exists('wp_redirect')) {
    /**
     * @param string $location
     * @param int $status
     * @param string $x_redirect_by
     * @return void
     */
    function wp_redirect($location, $status = 302, $x_redirect_by = 'WordPress') { return; }
}

if (!function_exists('add_query_arg')) {
    /**
     * @param string|array $key
     * @param string $value
     * @param string $url
     * @return string
     */
    function add_query_arg($key, $value = '', $url = '') { return $url; }
}

if (!function_exists('register_post_type')) {
    /**
     * @param string $post_type
     * @param array|string $args
     * @return WP_Post_Type|WP_Error
     */
    function register_post_type($post_type, $args = array()) { return null; }
}

if (!function_exists('add_menu_page')) {
    /**
     * @param string $page_title
     * @param string $menu_title
     * @param string $capability
     * @param string $menu_slug
     * @param callable $callback
     * @param string $icon_url
     * @param int $position
     * @return string
     */
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null) { return ''; }
}

if (!function_exists('add_submenu_page')) {
    /**
     * @param string $parent_slug
     * @param string $page_title
     * @param string $menu_title
     * @param string $capability
     * @param string $menu_slug
     * @param callable $callback
     * @param int $position
     * @return string|false
     */
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '', $position = null) { return ''; }
}

if (!function_exists('esc_html_e')) {
    /**
     * @param string $text
     * @param string $domain
     * @return void
     */
    function esc_html_e($text, $domain = 'default') { echo $text; }
}

if (!function_exists('settings_fields')) {
    /**
     * @param string $option_group
     * @return void
     */
    function settings_fields($option_group) { return; }
}

if (!function_exists('plugin_dir_url')) {
    /**
     * @param string $file
     * @return string
     */
    function plugin_dir_url($file) { return dirname($file) . '/'; }
}

if (!function_exists('sanitize_hex_color')) {
    /**
     * @param string $color
     * @return string|null
     */
    function sanitize_hex_color($color) { return $color; }
}

// WordPress Hooks
if (!function_exists('add_action')) {
    /**
     * @param string $tag
     * @param callable $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool
     */
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) { return true; }
}

if (!function_exists('add_filter')) {
    /**
     * @param string $tag
     * @param callable $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool
     */
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) { return true; }
}

if (!function_exists('do_action')) {
    /**
     * @param string $tag
     * @param mixed ...$args
     * @return void
     */
    function do_action($tag, ...$args) { return; }
}

if (!function_exists('apply_filters')) {
    /**
     * @param string $tag
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    function apply_filters($tag, $value, ...$args) { return $value; }
}

if (!function_exists('remove_action')) {
    /**
     * @param string $tag
     * @param callable $function_to_remove
     * @param int $priority
     * @return bool
     */
    function remove_action($tag, $function_to_remove, $priority = 10) { return true; }
}

if (!function_exists('remove_filter')) {
    /**
     * @param string $tag
     * @param callable $function_to_remove
     * @param int $priority
     * @return bool
     */
    function remove_filter($tag, $function_to_remove, $priority = 10) { return true; }
}

// WordPress Constants
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
}

if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

if (!defined('OBJECT_K')) {
    define('OBJECT_K', 'OBJECT_K');
}

if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', false);
}

if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', false);
}

if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', true);
}

// WordPress Classes (basic stubs)
if (!class_exists('WP_Error')) {
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_message($code = '') { return ''; }
        public function add($code, $message, $data = '') {}
    }
}

if (!class_exists('WP_Query')) {
    class WP_Query {
        public $posts = array();
        public $post_count = 0;
        public $found_posts = 0;
        public function __construct($args = array()) {}
        public function have_posts() { return false; }
        public function the_post() {}
        public function rewind_posts() {}
    }
}

if (!class_exists('wpdb')) {
    class wpdb {
        public $prefix = 'wp_';
        public $insert_id = 0;
        public function prepare($query, ...$args) { return $query; }
        public function get_results($query, $output = OBJECT) { return array(); }
        public function get_var($query) { return null; }
        public function get_row($query, $output = OBJECT, $y = 0) { return null; }
        public function query($query) { return 0; }
        public function insert($table, $data, $format = null) { return 1; }
        public function update($table, $data, $where, $format = null, $where_format = null) { return 1; }
        public function delete($table, $where, $where_format = null) { return 1; }
    }
}

// Global variables
if (!isset($wpdb)) {
    $wpdb = new wpdb();
}