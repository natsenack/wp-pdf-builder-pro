<?php
/**
 * WordPress Function Stubs for Intelephense IDE Recognition
 * 
 * This file provides declarations of WordPress core and utility functions
 * for IDE type checking and auto-completion. These are not executed at runtime.
 * 
 * @package PDF_Builder
 */

// WordPress Security & Sanitization Functions
if (!function_exists('wp_verify_nonce')) {
    /**
     * Verify that a nonce is valid
     * @param string $nonce
     * @param string|int $action
     * @return int|false
     */
    function wp_verify_nonce($nonce, $action = -1) {}
}

if (!function_exists('sanitize_text_field')) {
    /**
     * Sanitize a single string
     * @param string $str
     * @return string
     */
    function sanitize_text_field($str) {}
}

if (!function_exists('sanitize_textarea_field')) {
    /**
     * Sanitize a textarea field
     * @param string $str
     * @return string
     */
    function sanitize_textarea_field($str) {}
}

if (!function_exists('wp_kses')) {
    /**
     * Sanitize with allowed HTML tags
     * @param string $string
     * @param array $allowed_html
     * @param array $allowed_protocols
     * @return string
     */
    function wp_kses($string, $allowed_html = [], $allowed_protocols = []) {}
}

if (!function_exists('wp_kses_post')) {
    /**
     * Sanitize post content
     * @param string $data
     * @return string
     */
    function wp_kses_post($data) {}
}

if (!function_exists('absint')) {
    /**
     * Convert value to positive integer
     * @param mixed $maybeint
     * @return int
     */
    function absint($maybeint) {}
}

// WordPress Action/Filter Hooks
if (!function_exists('add_action')) {
    /**
     * Hook a function onto a specific action
     * @param string $hook
     * @param callable $function
     * @param int $priority
     * @param int $accepted_args
     * @return bool
     */
    function add_action($hook, $function, $priority = 10, $accepted_args = 1) {}
}

if (!function_exists('add_filter')) {
    /**
     * Hook a function onto a specific filter
     * @param string $hook
     * @param callable $function
     * @param int $priority
     * @param int $accepted_args
     * @return bool
     */
    function add_filter($hook, $function, $priority = 10, $accepted_args = 1) {}
}

if (!function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook
     * @param string $hook
     * @param mixed $arg
     * @return void
     */
    function do_action($hook, ...$arg) {}
}

if (!function_exists('apply_filters')) {
    /**
     * Apply filters to a value
     * @param string $hook
     * @param mixed $value
     * @param mixed $var
     * @return mixed
     */
    function apply_filters($hook, $value, ...$var) {}
}

// WordPress Internationalization (i18n)
if (!function_exists('__')) {
    /**
     * Retrieve translated string
     * @param string $text
     * @param string $domain
     * @return string
     */
    function __($text, $domain = 'default') {}
}

if (!function_exists('_e')) {
    /**
     * Display translated string
     * @param string $text
     * @param string $domain
     * @return void
     */
    function _e($text, $domain = 'default') {}
}

if (!function_exists('_x')) {
    /**
     * Retrieve context-based translation
     * @param string $text
     * @param string $context
     * @param string $domain
     * @return string
     */
    function _x($text, $context, $domain = 'default') {}
}

// WordPress Settings API
if (!function_exists('add_settings_section')) {
    /**
     * Add a new section to a settings page
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @param string $page
     * @return void
     */
    function add_settings_section($id, $title, $callback, $page) {}
}

if (!function_exists('add_settings_field')) {
    /**
     * Add a new field to a settings section
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @param string $page
     * @param string $section
     * @param array $args
     * @return void
     */
    function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = []) {}
}

if (!function_exists('register_setting')) {
    /**
     * Register a setting and its data
     * @param string $option_group
     * @param string $option_name
     * @param array $args
     * @return void
     */
    function register_setting($option_group, $option_name, $args = []) {}
}

if (!function_exists('get_option')) {
    /**
     * Retrieve option value from the database
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    function get_option($option, $default = false) {}
}

if (!function_exists('update_option')) {
    /**
     * Update option value in database
     * @param string $option
     * @param mixed $value
     * @return bool
     */
    function update_option($option, $value) {}
}

if (!function_exists('add_option')) {
    /**
     * Add option value to database
     * @param string $option
     * @param mixed $value
     * @param string $deprecated
     * @param string|bool $autoload
     * @return bool
     */
    function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {}
}

// WordPress Admin & Menu Functions
if (!function_exists('add_menu_page')) {
    /**
     * Add a top-level menu
     * @param string $page_title
     * @param string $menu_title
     * @param string $capability
     * @param string $menu_slug
     * @param callable $function
     * @param string $icon_url
     * @param int|float $position
     * @return string
     */
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {}
}

if (!function_exists('add_submenu_page')) {
    /**
     * Add a submenu page
     * @param string $parent_slug
     * @param string $page_title
     * @param string $menu_title
     * @param string $capability
     * @param string $menu_slug
     * @param callable $function
     * @return string
     */
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') {}
}

if (!function_exists('wp_enqueue_script')) {
    /**
     * Enqueue a script
     * @param string $handle
     * @param string|false $src
     * @param array $deps
     * @param string|bool|null $ver
     * @param bool|array $args
     * @return void
     */
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $args = false) {}
}

if (!function_exists('wp_enqueue_style')) {
    /**
     * Enqueue a stylesheet
     * @param string $handle
     * @param string|false $src
     * @param array $deps
     * @param string|bool|null $ver
     * @param string $media
     * @return void
     */
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {}
}

if (!function_exists('wp_localize_script')) {
    /**
     * Localize a script
     * @param string $handle
     * @param string $object_name
     * @param array $l10n
     * @return bool
     */
    function wp_localize_script($handle, $object_name, $l10n) {}
}

// WordPress File & Directory Functions
if (!function_exists('wp_upload_dir')) {
    /**
     * Get upload directory info
     * @param string|null $time
     * @param bool $create_dir
     * @return array
     */
    function wp_upload_dir($time = null, $create_dir = true) {}
}

if (!function_exists('wp_mkdir_p')) {
    /**
     * Create a directory with proper permissions
     * @param string $target
     * @return bool
     */
    function wp_mkdir_p($target) {}
}

if (!function_exists('wp_safe_remote_post')) {
    /**
     * Perform safe remote HTTP POST request
     * @param string $url
     * @param array $args
     * @return array|\WP_Error
     */
    function wp_safe_remote_post($url, $args = []) {}
}

if (!function_exists('wp_remote_retrieve_body')) {
    /**
     * Retrieve body from HTTP response
     * @param array|\WP_Error $response
     * @return string
     */
    function wp_remote_retrieve_body($response) {}
}

// WordPress Post Functions
if (!function_exists('get_post')) {
    /**
     * Retrieve post data
     * @param int|object $post
     * @param string $output
     * @param string $filter
     * @return object|array|null
     */
    function get_post($post = null, $output = OBJECT, $filter = 'raw') {}
}

if (!function_exists('wp_insert_post')) {
    /**
     * Insert or update a post
     * @param array|object $postarr
     * @param bool $wp_error
     * @return int|\WP_Error
     */
    function wp_insert_post($postarr = [], $wp_error = false) {}
}

if (!function_exists('wp_update_post')) {
    /**
     * Update a post
     * @param array|object $postarr
     * @param bool $wp_error
     * @return int|\WP_Error
     */
    function wp_update_post($postarr = [], $wp_error = false) {}
}

if (!function_exists('get_post_meta')) {
    /**
     * Retrieve post meta value
     * @param int $post_id
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    function get_post_meta($post_id, $key = '', $single = false) {}
}

if (!function_exists('update_post_meta')) {
    /**
     * Update post meta value
     * @param int $post_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @param mixed $prev_value
     * @return int|bool
     */
    function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') {}
}

// WordPress User Functions
if (!function_exists('get_current_user_id')) {
    /**
     * Get current user ID
     * @return int
     */
    function get_current_user_id() {}
}

if (!function_exists('current_user_can')) {
    /**
     * Check if current user has capability
     * @param string $capability
     * @param int|mixed $args
     * @return bool
     */
    function current_user_can($capability, ...$args) {}
}

// WordPress Database Functions
if (!function_exists('wp_json_encode')) {
    /**
     * Encode variable as JSON
     * @param mixed $data
     * @param int $options
     * @param int $depth
     * @return string|false
     */
    function wp_json_encode($data, $options = 0, $depth = 512) {}
}

// WordPress Plugin Functions
if (!function_exists('plugin_dir_path')) {
    /**
     * Get plugin directory path
     * @param string $file
     * @return string
     */
    function plugin_dir_path($file) {}
}

if (!function_exists('plugin_dir_url')) {
    /**
     * Get plugin directory URL
     * @param string $file
     * @return string
     */
    function plugin_dir_url($file) {}
}

// WordPress AJAX Functions
if (!function_exists('wp_send_json_success')) {
    /**
     * Send JSON success response
     * @param mixed $data
     * @param int $status_code
     * @param int $options
     * @return void
     */
    function wp_send_json_success($data = null, $status_code = 200, $options = 0) {}
}

if (!function_exists('wp_send_json_error')) {
    /**
     * Send JSON error response
     * @param mixed $data
     * @param int $status_code
     * @param int $options
     * @return void
     */
    function wp_send_json_error($data = '', $status_code = 400, $options = 0) {}
}

if (!function_exists('wp_send_json')) {
    /**
     * Send JSON response
     * @param mixed $response
     * @param int $status_code
     * @param int $options
     * @return void
     */
    function wp_send_json($response, $status_code = 200, $options = 0) {}
}

// WordPress Escaping & Output
if (!function_exists('esc_attr')) {
    /**
     * Escape attribute
     * @param string $text
     * @return string
     */
    function esc_attr($text) {}
}

if (!function_exists('esc_html')) {
    /**
     * Escape HTML
     * @param string $text
     * @return string
     */
    function esc_html($text) {}
}

if (!function_exists('esc_url')) {
    /**
     * Escape URL
     * @param string $url
     * @param string|array $protocols
     * @return string
     */
    function esc_url($url, $protocols = null) {}
}

if (!function_exists('wp_kses_allowed_html')) {
    /**
     * Get allowed HTML tags
     * @param string|array $context
     * @return array
     */
    function wp_kses_allowed_html($context = 'post') {}
}

// WordPress Admin Notices
if (!function_exists('add_action')) {
    /**
     * Add admin notice
     * @return void
     */
}

// WordPress Transients
if (!function_exists('get_transient')) {
    /**
     * Get transient value
     * @param string $transient
     * @return mixed
     */
    function get_transient($transient) {}
}

if (!function_exists('set_transient')) {
    /**
     * Set transient value
     * @param string $transient
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    function set_transient($transient, $value, $expiration = 0) {}
}

if (!function_exists('delete_transient')) {
    /**
     * Delete transient value
     * @param string $transient
     * @return bool
     */
    function delete_transient($transient) {}
}

// WooCommerce Functions
if (!function_exists('wc_get_order')) {
    /**
     * Get WooCommerce order
     * @param int|object $id
     * @return mixed
     */
    function wc_get_order($id) {}
}

if (!function_exists('wc_get_product')) {
    /**
     * Get WooCommerce product
     * @param int|object $product
     * @return mixed
     */
    function wc_get_product($product) {}
}

if (!function_exists('wc_price')) {
    /**
     * Format price for WooCommerce
     * @param float $price
     * @param array $args
     * @return string
     */
    function wc_price($price, $args = []) {}
}

if (!function_exists('wc_get_order_statuses')) {
    /**
     * Get WooCommerce order statuses
     * @param string $types
     * @return array
     */
    function wc_get_order_statuses($types = 'all') {}
}

if (!function_exists('wc_get_order_status_name')) {
    /**
     * Get translated WooCommerce order status name
     * @param string $status
     * @return string
     */
    function wc_get_order_status_name($status) {}
}

if (!function_exists('get_woocommerce_currency')) {
    /**
     * Get WooCommerce currency code
     * @return string
     */
    function get_woocommerce_currency() {}
}

// Additional WordPress Functions
if (!function_exists('esc_textarea')) {
    /**
     * Escape textarea content
     * @param string $text
     * @return string
     */
    function esc_textarea($text) {}
}

if (!function_exists('wp_get_current_user')) {
    /**
     * Get the currently authenticated user
     * @return \WP_User
     */
    function wp_get_current_user() {}
}

if (!function_exists('wp_die')) {
    /**
     * Kill WordPress execution and display message
     * @param string|mixed $message
     * @param string $title
     * @param int|string|array $args
     * @return void
     */
    function wp_die($message = '', $title = '', $args = []) {}
}

if (!function_exists('sanitize_email')) {
    /**
     * Sanitize email address
     * @param string $email
     * @return string
     */
    function sanitize_email($email) {}
}

if (!function_exists('add_settings_error')) {
    /**
     * Register a settings error for display
     * @param string $setting
     * @param string $code
     * @param string $message
     * @param string $type
     * @return void
     */
    function add_settings_error($setting, $code, $message, $type = 'error') {}
}

if (!function_exists('wp_redirect')) {
    /**
     * Redirect to a URL
     * @param string $location
     * @param int $status
     * @return bool
     */
    function wp_redirect($location, $status = 302) {}
}

if (!function_exists('add_query_arg')) {
    /**
     * Add query string parameter to URL
     * @param string|array $key
     * @param string|mixed $value
     * @param string $query
     * @return string
     */
    function add_query_arg($key, $value = '', $query = '') {}
}

if (!function_exists('register_post_type')) {
    /**
     * Register a custom post type
     * @param string $post_type
     * @param string|array $args
     * @return object|\WP_Error
     */
    function register_post_type($post_type, $args = []) {}
}

if (!function_exists('did_action')) {
    /**
     * Check if an action has been triggered at least once
     * @param string $hook
     * @return int
     */
    function did_action($hook) {}
}

if (!function_exists('sanitize_hex_color')) {
    /**
     * Sanitize hex color
     * @param string $color
     * @return string
     */
    function sanitize_hex_color($color) {}
}

if (!function_exists('wp_cache_delete')) {
    /**
     * Delete a cache entry
     * @param string $key
     * @param string $group
     * @return bool
     */
    function wp_cache_delete($key, $group = '') {}
}

if (!function_exists('delete_option')) {
    /**
     * Delete an option from the database
     * @param string $option
     * @return bool
     */
    function delete_option($option) {}
}

if (!function_exists('wp_cache_set')) {
    /**
     * Set a cache entry
     * @param string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     */
    function wp_cache_set($key, $data, $group = '', $expire = 0) {}
}

if (!function_exists('wp_cache_get')) {
    /**
     * Retrieve a cache entry
     * @param string $key
     * @param string $group
     * @param bool $force
     * @param bool $found
     * @return mixed
     */
    function wp_cache_get($key, $group = '', $force = false, &$found = null) {}
}

if (!function_exists('wp_cache_flush')) {
    /**
     * Flush all cache entries
     * @return bool
     */
    function wp_cache_flush() {}
}

if (!function_exists('current_time')) {
    /**
     * Get current time in WordPress timezone
     * @param string $type
     * @param int|bool $gmt
     * @return int|string
     */
    function current_time($type = 'mysql', $gmt = 0) {}
}

if (!function_exists('size_format')) {
    /**
     * Format bytes to human readable format
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    function size_format($bytes, $decimals = 0) {}
}

if (!function_exists('wp_rand')) {
    /**
     * Generate a random value
     * @param int $min
     * @param int $max
     * @return int
     */
    function wp_rand($min = 0, $max = 0) {}
}

if (!function_exists('get_temp_dir')) {
    /**
     * Get temporary directory path
     * @return string
     */
    function get_temp_dir() {}
}

if (!function_exists('admin_url')) {
    /**
     * Get admin URL
     * @param string $path
     * @param string $scheme
     * @return string
     */
    function admin_url($path = '', $scheme = 'admin') {}
}

if (!function_exists('wp_remote_head')) {
    /**
     * Perform HTTP HEAD request
     * @param string $url
     * @param array $args
     * @return array|\WP_Error
     */
    function wp_remote_head($url, $args = []) {}
}

if (!function_exists('is_wp_error')) {
    /**
     * Check if value is a WP_Error object
     * @param mixed $thing
     * @return bool
     */
    function is_wp_error($thing) {}
}

if (!function_exists('get_site_url')) {
    /**
     * Get site URL
     * @param int|null $blog_id
     * @param string $path
     * @param string $scheme
     * @return string
     */
    function get_site_url($blog_id = null, $path = '', $scheme = 'https') {}
}

if (!function_exists('is_multisite')) {
    /**
     * Check if WordPress is in multisite mode
     * @return bool
     */
    function is_multisite() {}
}

if (!function_exists('is_email')) {
    /**
     * Check if string is a valid email address
     * @param string $email
     * @param bool $deprecated
     * @return bool|string
     */
    function is_email($email, $deprecated = false) {}
}

if (!function_exists('esc_url_raw')) {
    /**
     * Sanitize and escape a URL for use in redirects and database storage
     * @param string $url
     * @param array $protocols
     * @return string
     */
    function esc_url_raw($url, $protocols = null) {}
}

if (!function_exists('wp_enqueue_media')) {
    /**
     * Enqueue media scripts and styles
     * @param array $args
     * @return void
     */
    function wp_enqueue_media($args = []) {}
}

if (!function_exists('is_ssl')) {
    /**
     * Check if connection is HTTPS
     * @return bool
     */
    function is_ssl() {}
}

if (!function_exists('wp_safe_redirect')) {
    /**
     * Safe redirect to a URL
     * @param string $location
     * @param int $status
     * @return bool
     */
    function wp_safe_redirect($location, $status = 302) {}
}

if (!function_exists('get_theme_mod')) {
    /**
     * Get theme modification value
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    function get_theme_mod($name, $default = false) {}
}

if (!function_exists('wp_get_attachment_image_url')) {
    /**
     * Get attachment image URL
     * @param int $attachment_id
     * @param string|array $size
     * @param bool $icon
     * @return string|false
     */
    function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail', $icon = false) {}
}

if (!function_exists('wp_next_scheduled')) {
    /**
     * Get next scheduled event timestamp
     * @param string $hook
     * @param array $args
     * @return int|bool
     */
    function wp_next_scheduled($hook, $args = []) {}
}

if (!function_exists('wp_schedule_event')) {
    /**
     * Schedule a recurring event
     * @param int $timestamp
     * @param string $recurrence
     * @param string $hook
     * @param array $args
     * @return bool|\WP_Error
     */
    function wp_schedule_event($timestamp, $recurrence, $hook, $args = []) {}
}

if (!function_exists('wp_unschedule_event')) {
    /**
     * Unschedule an event
     * @param int $timestamp
     * @param string $hook
     * @param array $args
     * @return int|bool
     */
    function wp_unschedule_event($timestamp, $hook, $args = []) {}
}

if (!function_exists('wp_script_is')) {
    /**
     * Check if a script has been enqueued
     * @param string $handle
     * @param string $status
     * @return bool
     */
    function wp_script_is($handle, $status = 'enqueued') {}
}

if (!function_exists('wp_script_add_data')) {
    /**
     * Add data to an enqueued script
     * @param string $handle
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    function wp_script_add_data($handle, $key, $value) {}
}

if (!function_exists('get_bloginfo')) {
    /**
     * Get blog information
     * @param string $show
     * @param string $filter
     * @return string
     */
    function get_bloginfo($show = '', $filter = 'raw') {}
}

if (!function_exists('sanitize_key')) {
    /**
     * Sanitize a key string
     * @param string $key
     * @return string
     */
    function sanitize_key($key) {}
}

if (!function_exists('get_file_data')) {
    /**
     * Get file header data
     * @param string $file
     * @param array $default_headers
     * @param string|false $context
     * @return array
     */
    function get_file_data($file, $default_headers = [], $context = false) {}
}

if (!function_exists('add_meta_box')) {
    /**
     * Add a meta box to post/page edit screen
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @param string|array|null $screen
     * @param string $context
     * @param string $priority
     * @param mixed $callback_args
     * @return void
     */
    function add_meta_box($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null) {}
}

if (!function_exists('wp_nonce_field')) {
    /**
     * Output a nonce field
     * @param string $action
     * @param string $name
     * @param bool $referer
     * @param bool $echo
     * @return string
     */
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {}
}

if (!function_exists('delete_post_meta')) {
    /**
     * Delete post metadata
     * @param int $post_id
     * @param string $meta_key
     * @param mixed $meta_value
     * @return bool
     */
    function delete_post_meta($post_id, $meta_key, $meta_value = '') {}
}

if (!function_exists('get_posts')) {
    /**
     * Retrieve posts from database
     * @param array $args
     * @return array|int
     */
    function get_posts($args = []) {}
}

if (!function_exists('wp_delete_post')) {
    /**
     * Delete a post
     * @param int|WP_Post $post_id
     * @param bool $force_delete
     * @return WP_Post|false
     */
    function wp_delete_post($post_id = null, $force_delete = false) {}
}

if (!function_exists('is_user_logged_in')) {
    /**
     * Check if user is logged in
     * @return bool
     */
    function is_user_logged_in() {}
}

if (!function_exists('wp_remote_get')) {
    /**
     * Perform HTTP GET request
     * @param string $url
     * @param array $args
     * @return array|\WP_Error
     */
    function wp_remote_get($url, $args = []) {}
}

if (!function_exists('dbDelta')) {
    /**
     * Create or update database tables
     * @param string|array $queries
     * @param bool $execute
     * @return array
     */
    function dbDelta($queries = '', $execute = true) {}
}

// Custom PDF Builder functions
if (!function_exists('pdf_builder_is_woocommerce_active')) {
    /**
     * Check if WooCommerce is active
     * @return bool
     */
    function pdf_builder_is_woocommerce_active() {}
}

// WordPress Classes
if (!class_exists('WP_Error')) {
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_code() {}
        public function get_error_message($code = '') {}
        public function get_error_data($code = '') {}
    }
}

if (!class_exists('WP_User')) {
    class WP_User {
        public $ID = 0;
        public $caps = [];
        public $cap_key = '';
        public $roles = [];
        public $allcaps = [];
    }
}

// WordPress Constants
if (!defined('WP_CLI')) {
    define('WP_CLI', false);
}

if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}

// WordPress Constants
if (!defined('WC_VERSION')) {
    define('WC_VERSION', '0.0.0');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', false);
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(__DIR__) . '/wp-content');
}

if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
}

// File and Attachment Functions
if (!function_exists('sanitize_file_name')) {
    /**
     * Sanitize a filename
     * @param string $filename
     * @return string
     */
    function sanitize_file_name($filename) {}
}

if (!function_exists('wp_get_attachment_url')) {
    /**
     * Get the URL of a media attachment
     * @param int $attachment_id
     * @return string|false
     */
    function wp_get_attachment_url($attachment_id) {}
}

if (!function_exists('wp_get_attachment_metadata')) {
    /**
     * Get attachment metadata
     * @param int $attachment_id
     * @param bool $unfiltered
     * @return array|false
     */
    function wp_get_attachment_metadata($attachment_id, $unfiltered = false) {}
}

// Dompdf stubs
namespace Dompdf {
    if (!class_exists('\Dompdf\Dompdf')) {
        class Dompdf {
            public function __construct() {}
            public function loadHtml($html, $encoding = null) {}
            public function render() {}
            public function output() {}
            public function stream($filename = 'document.pdf', $options = []) {}
            public function getCanvas() {}
            public function setProtocol($protocol = 'file://') {}
            public function setPaper($paper = 'letter', $orientation = 'portrait') {}
        }
    }

    if (!class_exists('\Dompdf\Options')) {
        class Options {
            public function __construct(array $options = []) {}
            public function set(string $key, $value) {}
            public function get(string $key) {}
            public function setFontDir($path) {}
            public function setFontCache($path) {}
        }
    }
}

if (!function_exists('wp_generate_password')) {
    /**
     * Generate a random password
     * @param int $length
     * @param bool $special_chars
     * @return string
     */
    function wp_generate_password($length = 12, $special_chars = true) {}
}

if (!function_exists('date_i18n')) {
    /**
     * Retrieve the date in localized format
     * @param string $format
     * @param int|bool $timestamp
     * @param bool $gmt
     * @return string
     */
    function date_i18n($format, $timestamp = false, $gmt = false) {}
}

if (!function_exists('wp_schedule_single_event')) {
    /**
     * Schedule an event to run once
     * @param int $timestamp
     * @param string $hook
     * @param array $args
     * @return false|void
     */
    function wp_schedule_single_event($timestamp, $hook, $args = []) {}
}

if (!function_exists('wp_tempnam')) {
    /**
     * Get a temporary file name
     * @param string $dir
     * @param string $prefix
     * @return string|false
     */
    function wp_tempnam($dir = '', $prefix = '') {}
}

if (!function_exists('wp_mail')) {
    /**
     * Send mail
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param string|array $headers
     * @param string|array $attachments
     * @return bool
     */
    function wp_mail($to, $subject, $message, $headers = '', $attachments = []) {}
}

if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

// WordPress Exception class
if (!class_exists('Exception')) {
    class Exception extends \Exception {}
}
