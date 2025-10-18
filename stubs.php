<?php
/**
 * Stubs WordPress pour Intelephense
 * Ce fichier aide Intelephense à reconnaître les fonctions WordPress globales
 * sans inclure réellement le code WordPress
 */

// Constantes WordPress
if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 60 * 60);
}
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 24 * 60 * 60);
}
if (!defined('WEEK_IN_SECONDS')) {
    define('WEEK_IN_SECONDS', 7 * 24 * 60 * 60);
}
if (!defined('MONTH_IN_SECONDS')) {
    define('MONTH_IN_SECONDS', 30 * 24 * 60 * 60);
}
if (!defined('YEAR_IN_SECONDS')) {
    define('YEAR_IN_SECONDS', 365 * 24 * 60 * 60);
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}
if (!defined('PDF_BUILDER_DEV_MODE')) {
    define('PDF_BUILDER_DEV_MODE', false);
}
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}
if (!defined('PDF_PAGE_ORIENTATION')) {
    define('PDF_PAGE_ORIENTATION', 'P');
}
if (!defined('PDF_UNIT')) {
    define('PDF_UNIT', 'mm');
}
if (!defined('PDF_PAGE_FORMAT')) {
    define('PDF_PAGE_FORMAT', 'A4');
}

// Fonctions WordPress stubifiées
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('_x')) {
    function _x($text, $context, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return false;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return false;
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 0;
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return null;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = []) {
        die($message);
    }
}

if (!function_exists('wp_safe_remote_get')) {
    function wp_safe_remote_get($url, $args = []) {
        return [];
    }
}

if (!function_exists('wp_safe_remote_post')) {
    function wp_safe_remote_post($url, $args = []) {
        return [];
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {
        return '';
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') {
        return '';
    }
}

if (!function_exists('add_meta_box')) {
    function add_meta_box($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null) {
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return false;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return false;
    }
}

if (!function_exists('get_transient')) {
    function get_transient($transient) {
        return false;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        return false;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        return true;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        return true;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return '';
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return false;
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        return '';
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return $str;
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {
        return $str;
    }
}

if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {
        return $color;
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return $email;
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return $text;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return $url;
    }
}

if (!function_exists('esc_js')) {
    function esc_js($text) {
        return $text;
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return '';
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return '';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return '';
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir($time = null, $create_dir = true) {
        return [];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($pathname, $mode = 0777) {
        return true;
    }
}

if (!function_exists('add_settings_error')) {
    function add_settings_error($setting, $code, $message, $type = 'error') {
        return null;
    }
}

if (!function_exists('submit_button')) {
    function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {
        return '';
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        return '';
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {
        return '';
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = [], $status_code = 200, $flags = 0) {
        return '';
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = [], $status_code = 400, $flags = 0) {
        return '';
    }
}

if (!function_exists('wp_send_json')) {
    function wp_send_json($response, $status_code = null, $flags = 0) {
        return '';
    }
}

if (!function_exists('wp_cache_flush')) {
    function wp_cache_flush() {
        return true;
    }
}

if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        return '';
    }
}

if (!function_exists('absint')) {
    function absint($maybeint) {
        return 0;
    }
}

if (!function_exists('size_format')) {
    function size_format($bytes, $decimals = 0) {
        return '';
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '') {
        return '';
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        return $value;
    }
}

if (!function_exists('do_action')) {
    function do_action($tag) {
        return null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('get_current_screen')) {
    function get_current_screen() {
        return null;
    }
}

if (!function_exists('wp_add_inline_script')) {
    function wp_add_inline_script($handle, $data = '', $position = 'after') {
        return true;
    }
}

if (!function_exists('__checked_selected_helper')) {
    function __checked_selected_helper($helper, $current, $echo, $type) {
        return '';
    }
}

// Fonctions WooCommerce
if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        return null;
    }
}

if (!function_exists('wc_get_order_status_name')) {
    function wc_get_order_status_name($status) {
        return $status;
    }
}

if (!function_exists('wc_price')) {
    function wc_price($price, $args = []) {
        return (string) $price;
    }
}

// Fonctions WordPress - Thème/Media
if (!function_exists('get_theme_mod')) {
    function get_theme_mod($name, $default = false) {
        return $default;
    }
}

if (!function_exists('wp_get_attachment_image_url')) {
    function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail') {
        return '';
    }
}

if (!function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = '') {
        return '';
    }
}

if (!function_exists('get_the_post_thumbnail_url')) {
    function get_the_post_thumbnail_url($post = null, $size = 'post-thumbnail') {
        return '';
    }
}

// Classe Exception stub
if (!class_exists('Exception')) {
    class Exception {
        public function __construct($message = "", $code = 0) {}
        public function getMessage() { return ''; }
        public function getCode() { return 0; }
    }
}

// Classe Error stub
if (!class_exists('Error')) {
    class Error {
        public function __construct($message = "", $code = 0) {}
        public function getMessage() { return ''; }
        public function getCode() { return 0; }
    }
}

// Classes PDF Builder
if (!class_exists('PDF_Generator')) {
    class PDF_Generator {
        public function generate($html = '') { return ''; }
        public function generate_from_elements($elements = []) { return ''; }
    }
}

if (!class_exists('PDF_Builder_Cache_Manager')) {
    class PDF_Builder_Cache_Manager {
        public static function getInstance() { return new self(); }
        public function get($key) { return null; }
        public function set($key, $value, $ttl = 3600) { return true; }
    }
}

if (!function_exists('pdf_builder_create_database_tables')) {
    function pdf_builder_create_database_tables() {
        return true;
    }
}

// Fonction WC() pour WooCommerce
if (!function_exists('WC')) {
    function WC() {
        return null;
    }
}

// Classes PHP SPL
if (!class_exists('RecursiveIteratorIterator')) {
    class RecursiveIteratorIterator {
        const SKIP_DOTS = 0;
        public function __construct($iterator, $mode = 0) {}
    }
}

if (!class_exists('RecursiveDirectoryIterator')) {
    class RecursiveDirectoryIterator {
        const SKIP_DOTS = 4;
        public function __construct($path) {}
    }
}

// Classes WordPress
if (!class_exists('WP_Error')) {
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_code() { return ''; }
        public function get_error_message($code = '') { return ''; }
        public function get_error_data($code = '') { return null; }
    }
}

if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action = -1, $query_arg = '_wpnonce') {
        return false;
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        return '';
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo esc_attr($text);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('translate_user_role')) {
    function translate_user_role($name, $domain = 'default') {
        return __($name, $domain);
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'nonce_' . md5($action);
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $context = 'display') {
        return $url;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '', $filter = 'raw') {
        return 'Test Blog';
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return 'http://example.com/wp-admin/' . $path;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        return;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        return;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        return;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = []) {
        die($message);
    }
}

if (!function_exists('wp_redirect')) {
    function wp_redirect($location, $status = 302, $x_redirect_by = 'WordPress') {
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect($location, $status = 302) {
        wp_redirect($location, $status);
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return true;
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) {
        return $data;
    }
}

if (!function_exists('wp_kses')) {
    function wp_kses($string, $allowed_html, $allowed_protocols = []) {
        return $string;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof \WP_Error;
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null) {
        return 'http://example.com' . $path;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('wp_send_json')) {
    function wp_send_json($response = null, $status_code = null) {
        wp_die('', '', ['response' => $status_code, 'exit' => false]);
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        wp_send_json(['success' => true, 'data' => $data]);
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = null) {
        wp_send_json(['success' => false, 'data' => $data], $status_code);
    }
}

// Fonctions WooCommerce
if (!function_exists('WC')) {
    function WC() {
        return null;
    }
}

if (!function_exists('wc_get_order_statuses')) {
    function wc_get_order_statuses() {
        return [
            'wc-pending' => 'En attente',
            'wc-processing' => 'En cours',
            'wc-on-hold' => 'En attente',
            'wc-completed' => 'Terminée',
            'wc-cancelled' => 'Annulée',
            'wc-refunded' => 'Remboursée',
            'wc-failed' => 'Échec'
        ];
    }
}

if (!function_exists('wc_price')) {
    function wc_price($price, $args = []) {
        return '$' . number_format($price, 2);
    }
}

if (!function_exists('wc_format_decimal')) {
    function wc_format_decimal($number, $dp = false, $trim_zeros = false) {
        return number_format($number, $dp ?: 2);
    }
}

if (!function_exists('wc_get_product')) {
    function wc_get_product($product_id) {
        return null;
    }
}

if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        return null;
    }
}

// Classes WordPress
if (!class_exists('WP_Error')) {
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_message($code = '') { return ''; }
        public function add($code, $message, $data = '') {}
        public function has_errors() { return false; }
    }
}

if (!class_exists('WP_Post')) {
    class WP_Post {
        public $ID;
        public $post_title;
        public $post_content;
        public $post_excerpt;
        public $post_status;
        public $post_type;
        public $post_author;
        public $post_date;
        public $post_modified;
    }
}

if (!class_exists('WP_User')) {
    class WP_User {
        public $ID;
        public $user_login;
        public $user_email;
        public $display_name;
        public $roles = [];
        
        public function __construct($id = 0, $name = '', $blog_id = '') {}
        public function has_cap($cap) { return false; }
        public function get_role_caps() { return []; }
    }
}

if (!class_exists('WP_Roles')) {
    class WP_Roles {
        public $roles = [];
        
        public function __construct() {}
        public function get_names() { return []; }
        public function get_role($role) { return null; }
    }
}

if (!class_exists('WP_Query')) {
    class WP_Query {
        public $posts = [];
        public $post_count = 0;
        public $found_posts = 0;
        public $max_num_pages = 0;
        
        public function __construct($query = '') {}
        public function have_posts() { return false; }
        public function the_post() {}
        public function rewind_posts() {}
    }
}

// Classes PDF Builder
if (!class_exists('PDF_Builder_Pro_Generator')) {
    class PDF_Builder_Pro_Generator {
        public function generate($html = '') { return ''; }
    }
}

// Déclarations pour le namespace PDF_Builder\Core (utilisées avec \ préfixe)
if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function) {
        return;
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function) {
        return;
    }
}

if (!function_exists('register_setting')) {
    function register_setting($option_group, $option_name, $args = []) {
        return;
    }
}

if (!function_exists('add_settings_section')) {
    function add_settings_section($id, $title, $callback, $page) {
        return;
    }
}

if (!function_exists('settings_fields')) {
    function settings_fields($option_group) {
        return;
    }
}

if (!function_exists('do_settings_sections')) {
    function do_settings_sections($page) {
        return;
    }
}

if (!function_exists('dbDelta')) {
    function dbDelta($queries, $execute = true) {
        return [];
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return $value;
    }
}

if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($filename) {
        return $filename;
    }
}
