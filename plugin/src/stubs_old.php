<?php
/**
 * Stubs for external functions and constants to satisfy Intelephense
 * This file is for development only and should not be deployed to production
 */

// WordPress constants
if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', false);
}
if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}
if (!defined('ABSPATH')) {
    define('ABSPATH', '/path/to/wordpress/');
}
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

// WooCommerce functions
if (!function_exists('wc_get_order')) {
    /**
     * @param  int $order_id
     * @return mixed
     */
    function wc_get_order($order_id)
    {
        return null; 
    }
}

if (!function_exists('wc_get_order_statuses')) {
    /**
     * @return array
     */
    function wc_get_order_statuses()
    {
        return []; 
    }
}

if (!function_exists('wc_price')) {
    /**
     * @param  float $price
     * @return string
     */
    function wc_price($price)
    {
        return ''; 
    }
}

if (!function_exists('wc_get_order_status_name')) {
    /**
     * @param  string $status
     * @return string
     */
    function wc_get_order_status_name($status)
    {
        return ''; 
    }
}

if (!function_exists('wc_get_product')) {
    /**
     * @param  int $product_id
     * @return mixed
     */
    function wc_get_product($product_id)
    {
        return null; 
    }
}

if (!function_exists('get_woocommerce_currency')) {
    /**
     * @return string
     */
    function get_woocommerce_currency()
    {
        return ''; 
    }
}

// WordPress constants
if (!defined('WP_CLI')) {
    define('WP_CLI', false);
}

if (!defined('WC_VERSION')) {
    define('WC_VERSION', '0.0.0');
}

if (!defined('DISABLE_WP_CRON')) {
    define('DISABLE_WP_CRON', false);
}

// Custom functions
if (!function_exists('pdf_builder_is_woocommerce_active')) {
    /**
     * @return bool
     */
    function pdf_builder_is_woocommerce_active()
    {
        return false; 
    }
}

if (!function_exists('pdf_builder_run_migrations')) {
    /**
     * @param string $version
     */
    function pdf_builder_run_migrations($version)
    {
    }
}

// PHP native functions that Intelephense might not recognize
if (!function_exists('rand')) {
    /**
     * @param  int $min
     * @param  int $max
     * @return int
     */
    function rand($min = 0, $max = PHP_INT_MAX)
    {
        return 0; 
    }
}

// WordPress core functions
if (!function_exists('wp_kses_post')) {
    /**
     * @param  string $content
     * @return string
     */
    function wp_kses_post($content)
    {
        return $content;
    }
}

if (!function_exists('wp_verify_nonce')) {
    /**
     * @param  string $nonce
     * @param  string $action
     * @return bool
     */
    function wp_verify_nonce($nonce, $action)
    {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    /**
     * @param  string $capability
     * @return bool
     */
    function current_user_can($capability)
    {
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    /**
     * @param  string $str
     * @return string
     */
    function sanitize_text_field($str)
    {
        return $str;
    }
}

if (!function_exists('get_option')) {
    /**
     * @param  string $option
     * @param  mixed $default
     * @return mixed
     */
    function get_option($option, $default = false)
    {
        return $default;
    }
}

if (!function_exists('add_action')) {
    /**
     * @param  string $tag
     * @param  callable $function_to_add
     * @param  int $priority
     * @param  int $accepted_args
     * @return bool
     */
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return true;
    }
}

if (!function_exists('update_option')) {
    /**
     * @param  string $option
     * @param  mixed $value
     * @return bool
     */
    function update_option($option, $value)
    {
        return true;
    }
}

if (!function_exists('delete_option')) {
    /**
     * @param  string $option
     * @return bool
     */
    function delete_option($option)
    {
        return true;
    }
}

if (!function_exists('is_ssl')) {
    /**
     * @return bool
     */
    function is_ssl()
    {
        return false;
    }
}

if (!function_exists('wp_safe_redirect')) {
    /**
     * @param  string $location
     * @param  int $status
     * @return void
     */
    function wp_safe_redirect($location, $status = 302)
    {
    }
}

if (!function_exists('register_setting')) {
    /**
     * @param  string $option_group
     * @param  string $option_name
     * @param  array $args
     * @return void
     */
    function register_setting($option_group, $option_name, $args = array())
    {
    }
}

if (!function_exists('wp_localize_script')) {
    /**
     * @param  string $handle
     * @param  string $object_name
     * @param  array $l10n
     * @return bool
     */
    function wp_localize_script($handle, $object_name, $l10n)
    {
        return true;
    }
}

if (!function_exists('admin_url')) {
    /**
     * @param  string $path
     * @return string
     */
    function admin_url($path = '')
    {
        return $path;
    }
}

if (!function_exists('wp_create_nonce')) {
    /**
     * @param  string $action
     * @return string
     */
    function wp_create_nonce($action)
    {
        return 'nonce';
    }
}

if (!function_exists('__')) {
    /**
     * @param  string $text
     * @param  string $domain
     * @return string
     */
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('wp_enqueue_script')) {
    /**
     * @param  string $handle
     * @param  string $src
     * @param  array $deps
     * @param  string|bool $ver
     * @param  bool $in_footer
     * @return void
     */
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false)
    {
    }
}

if (!function_exists('is_admin')) {
    /**
     * @return bool
     */
    function is_admin()
    {
        return false;
    }
}

if (!function_exists('wp_doing_ajax')) {
    /**
     * @return bool
     */
    function wp_doing_ajax()
    {
        return false;
    }
}

if (!function_exists('is_user_logged_in')) {
    /**
     * @return bool
     */
    function is_user_logged_in()
    {
        return true;
    }
}

if (!function_exists('wp_die')) {
    /**
     * @param  string $message
     * @return void
     */
    function wp_die($message = '')
    {
        die($message);
    }
}

if (!function_exists('add_option')) {
    /**
     * @param  string $option
     * @param  mixed $value
     * @param  string $deprecated
     * @param  string $autoload
     * @return bool
     */
    function add_option($option, $value, $deprecated = '', $autoload = 'yes')
    {
        return true;
    }
}

if (!function_exists('wp_send_json_error')) {
    /**
     * @param  mixed $data
     * @return void
     */
    function wp_send_json_error($data = null)
    {
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('wp_send_json_success')) {
    /**
     * @param  mixed $data
     * @return void
     */
    function wp_send_json_success($data = null)
    {
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('get_post')) {
    /**
     * @param  int|WP_Post|null $post
     * @param  string $output
     * @param  string $filter
     * @return WP_Post|null
     */
    function get_post($post = null, $output = 'OBJECT', $filter = 'raw')
    {
        return null;
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * @param  int $post_id
     * @param  string $key
     * @param  bool $single
     * @return mixed
     */
    function get_post_meta($post_id, $key = '', $single = false)
    {
        return $single ? '' : array();
    }
}

if (!function_exists('get_theme_mod')) {
    /**
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    function get_theme_mod($name, $default = false)
    {
        return $default;
    }
}

if (!function_exists('wp_get_attachment_image_url')) {
    /**
     * @param  int $attachment_id
     * @param  string|array $size
     * @return string|false
     */
    function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail')
    {
        return '';
    }
}

if (!function_exists('plugin_dir_url')) {
    /**
     * @param  string $file
     * @return string
     */
    function plugin_dir_url($file)
    {
        return '';
    }
}

if (!function_exists('plugin_basename')) {
    /**
     * @param  string $file
     * @return string
     */
    function plugin_basename($file)
    {
        return '';
    }
}

if (!function_exists('deactivate_plugins')) {
    /**
     * @param  string|array $plugins
     * @param  bool $silent
     * @param  mixed $network_wide
     * @return void
     */
    function deactivate_plugins($plugins, $silent = false, $network_wide = null)
    {
    }
}

if (!function_exists('get_bloginfo')) {
    /**
     * @param  string $show
     * @return string
     */
    function get_bloginfo($show = '')
    {
        return '';
    }
}

if (!function_exists('dbDelta')) {
    /**
     * @param  string|array $queries
     * @param  bool $execute
     * @return array
     */
    function dbDelta($queries = '', $execute = true)
    {
        return [];
    }
}

if (!function_exists('get_current_user_id')) {
    /**
     * @return int
     */
    function get_current_user_id()
    {
        return 1;
    }
}

if (!function_exists('wp_mkdir_p')) {
    /**
     * @param  string $dir
     * @return bool
     */
    function wp_mkdir_p($dir)
    {
        return true;
    }
}

if (!function_exists('current_time')) {
    /**
     * @param  string $type
     * @param  int $gmt
     * @return string|int
     */
    function current_time($type, $gmt = 0)
    {
        return time();
    }
}

if (!function_exists('wp_date')) {
    /**
     * @param  string $format
     * @param  int $timestamp
     * @param  DateTimeZone|null $timezone
     * @return string
     */
    function wp_date($format, $timestamp = null, $timezone = null)
    {
        return date($format, $timestamp ?: time());
    }
}

if (!function_exists('wp_timezone_string')) {
    /**
     * @return string
     */
    function wp_timezone_string()
    {
        return 'UTC';
    }
}

if (!function_exists('maybe_unserialize')) {
    /**
     * @param  string $original
     * @return mixed
     */
    function maybe_unserialize($original)
    {
        return $original;
    }
}

if (!function_exists('size_format')) {
    /**
     * @param  int|string $bytes
     * @param  int $decimals
     * @return string
     */
    function size_format($bytes, $decimals = 0)
    {
        return '';
    }
}

if (!function_exists('sanitize_file_name')) {
    /**
     * @param  string $filename
     * @return string
     */
    function sanitize_file_name($filename)
    {
        return $filename;
    }
}

if (!function_exists('wp_send_json')) {
    /**
     * @param  mixed $response
     * @param  int $status_code
     * @param  int $options
     * @return void
     */
    function wp_send_json($response, $status_code = null, $options = 0)
    {
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('wp_upload_dir')) {
    /**
     * @param  string $time
     * @return array
     */
    function wp_upload_dir($time = null)
    {
        return [
            'path' => '',
            'url' => '',
            'subdir' => '',
            'basedir' => '',
            'baseurl' => '',
            'error' => false,
        ];
    }
}

if (!function_exists('plugin_dir_path')) {
    /**
     * @param  string $file
     * @return string
     */
    function plugin_dir_path($file)
    {
        return '';
    }
}
