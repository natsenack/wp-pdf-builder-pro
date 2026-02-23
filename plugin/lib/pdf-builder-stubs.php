<?php
/**
 * PDF Builder Pro - Unified Function Stubs
 * 
 * This file provides comprehensive declarations of WordPress, Dompdf, TCPDF,
 * and custom PDF Builder functions for IDE type checking and auto-completion.
 * These are NOT executed at runtime - they're purely for IDE support.
 * 
 * @package PDF_Builder
 * @version 1.0.0
 */

// IMPORTANT: This file is ONLY for IDE type checking and auto-completion.
// It should NOT be loaded at runtime. If you see this message, something is wrong.
if (defined('ABSPATH')) {
    // This means WordPress has loaded, so we should NOT define these functions
    // They are already defined by WordPress core
    return;
}

// ============================================================================
// WORDPRESS CORE CONSTANTS
// ============================================================================

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(__DIR__) . '/wp-content');
}

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

if (!defined('WEEK_IN_SECONDS')) {
    define('WEEK_IN_SECONDS', 604800);
}

if (!defined('MONTH_IN_SECONDS')) {
    define('MONTH_IN_SECONDS', 2592000);
}

if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', false);
}

if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', false);
}

if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '40M');
}

if (!defined('WP_MAX_MEMORY_LIMIT')) {
    define('WP_MAX_MEMORY_LIMIT', '256M');
}

if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', false);
}

if (!defined('DISABLE_WP_CRON')) {
    define('DISABLE_WP_CRON', false);
}

if (!defined('PDF_BUILDER_REQUEST_START')) {
    define('PDF_BUILDER_REQUEST_START', microtime(true));
}

// ============================================================================
// WORDPRESS SECURITY & NONCE FUNCTIONS
// ============================================================================

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {}
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {}
}

if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = []) {}
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = 400) {}
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null, $status_code = 200) {}
}

// ============================================================================
// WORDPRESS SANITIZATION & ESCAPING FUNCTIONS
// ============================================================================

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {}
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {}
}

if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($filename) {}
}

if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($class = '') {}
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {}
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {}
}

if (!function_exists('wp_kses')) {
    function wp_kses($string, $allowed_html = [], $allowed_protocols = []) {}
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) {}
}

if (!function_exists('esc_html')) {
    function esc_html($text) {}
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {}
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {}
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {}
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {}
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {}
}

if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $context = 'display') {}
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url, $protocols = null) {}
}

if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {}
}

if (!function_exists('absint')) {
    function absint($maybeint) {}
}

if (!function_exists('intval')) {
    function intval($var, $base = 10) {}
}

// ============================================================================
// WORDPRESS ACTION & FILTER HOOK FUNCTIONS
// ============================================================================

if (!function_exists('add_action')) {
    function add_action($hook, $function, $priority = 10, $accepted_args = 1) {}
}

if (!function_exists('do_action')) {
    function do_action($hook, $arg = '') {}
}

if (!function_exists('remove_action')) {
    function remove_action($hook, $function, $priority = 10) {}
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $function, $priority = 10, $accepted_args = 1) {}
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value) {}
}

if (!function_exists('remove_filter')) {
    function remove_filter($hook, $function, $priority = 10) {}
}

// ============================================================================
// WORDPRESS OPTIONS & TRANSIENTS
// ============================================================================

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {}
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {}
}

if (!function_exists('delete_option')) {
    function delete_option($option) {}
}

if (!function_exists('get_transient')) {
    function get_transient($transient) {}
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0): bool { return true; }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient): bool { return true; }
}

if (!function_exists('get_site_transient')) {
    function get_site_transient($transient) {}
}

if (!function_exists('set_site_transient')) {
    function set_site_transient($transient, $value, $expiration = 0): bool { return true; }
}

if (!function_exists('delete_site_transient')) {
    function delete_site_transient($transient): bool { return true; }
}

// ============================================================================
// WORDPRESS DATABASE FUNCTIONS
// ============================================================================

if (!function_exists('get_site_url')) {
    function get_site_url($blog_id = null, $path = '', $scheme = null): string { return ''; }
}

if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null): string { return ''; }
}

if (!function_exists('wp_remote_request')) {
    function wp_remote_request($url, $args = []) {}
}

if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args = []) {}
}

if (!function_exists('wp_remote_get')) {
    function wp_remote_get($url, $args = []) {}
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {}
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {}
}

// ============================================================================
// WORDPRESS USER & CAPABILITY FUNCTIONS
// ============================================================================

if (!function_exists('current_user_can')) {
    function current_user_can($capability, $arg = null): bool { return false; }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id(): int { return 0; }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {}
}

if (!function_exists('is_plugin_active')) {
    function is_plugin_active($plugin): bool { return false; }
}

if (!function_exists('is_plugin_active_for_network')) {
    function is_plugin_active_for_network($plugin): bool { return false; }
}

if (!function_exists('activate_plugins')) {
    function activate_plugins($plugins, $redirect = '', $network_wide = false, $silent = false) {}
}

if (!function_exists('deactivate_plugins')) {
    function deactivate_plugins($plugins, $redirect = '', $network_wide = false, $silent = false) {}
}

if (!function_exists('get_user_by')) {
    function get_user_by($field, $value) {}
}

if (!function_exists('get_users')) {
    function get_users($args = []) {}
}

if (!function_exists('get_core_updates')) {
    function get_core_updates() {}
}

if (!function_exists('get_plugin_updates')) {
    function get_plugin_updates() {}
}

// ============================================================================
// WORDPRESS PLUGIN FUNCTIONS
// ============================================================================

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {}
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {}
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {}
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {}
}

// ============================================================================
// WORDPRESS ATTACHMENT & MEDIA FUNCTIONS
// ============================================================================

if (!function_exists('wp_get_attachment_url')) {
    function wp_get_attachment_url($attachment_id) {}
}

if (!function_exists('wp_get_attachment_metadata')) {
    function wp_get_attachment_metadata($attachment_id, $unfiltered = false) {}
}

// ============================================================================
// WORDPRESS SCHEDULING FUNCTIONS
// ============================================================================

if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($timestamp, $recurrence, $hook, $args = []) {}
}

if (!function_exists('wp_schedule_single_event')) {
    function wp_schedule_single_event($timestamp, $hook, $args = []) {}
}

if (!function_exists('wp_clear_scheduled_hook')) {
    function wp_clear_scheduled_hook($hook, $args = []) {}
}

if (!function_exists('wp_unschedule_hook')) {
    function wp_unschedule_hook($hook) {}
}

// ============================================================================
// WORDPRESS UTILITY FUNCTIONS
// ============================================================================

if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length = 12, $special_chars = true): string { return ''; }
}

// NOTE: wp_tempnam was added in WordPress 6.7 - removed stub to avoid redeclaration conflict
// WordPress 6.7+ provides native wp_tempnam(), so we don't need a stub

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512): string { return ''; }
}

if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = []): bool { return false; }
}

if (!function_exists('wp_cache_flush')) {
    function wp_cache_flush(): void {}
}

if (!function_exists('current_time')) {
    function current_time($type = 'mysql', $gmt = 0): string { return ''; }
}

if (!function_exists('date_i18n')) {
    function date_i18n($format, $timestamp = false, $gmt = false): string { return ''; }
}

if (!function_exists('wp_add_inline_style')) {
    function wp_add_inline_style($handle, $data = ''): bool { return true; }
}

if (!function_exists('is_singular')) {
    function is_singular($post_types = ''): bool { return false; }
}

if (!function_exists('has_shortcode')) {
    function has_shortcode($post_id, $tag): bool { return false; }
}

if (!function_exists('wp_date')) {
    function wp_date($format, $timestamp = 0, $timezone = null): string { return ''; }
}

if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($data) {}
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function): void {}
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function): void {}
}

if (!function_exists('add_role')) {
    function add_role($role, $display_name = '', $capabilities = []) {}
}

if (!function_exists('remove_role')) {
    function remove_role($role): bool { return false; }
}

if (!function_exists('get_role')) {
    function get_role($role) {}
}

if (!function_exists('delete_user_meta')) {
    function delete_user_meta($user_id, $meta_key, $meta_value = ''): bool { return true; }
}

if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action = -1, $query_arg = false, $die = true) {}
}

if (!function_exists('has_action')) {
    function has_action($hook, $function_to_check = false) { return false; }
}

if (!function_exists('user_can')) {
    function user_can($user_id, $capability) { return false; }
}

if (!function_exists('get_num_queries')) {
    function get_num_queries(): int { return 0; }
}

if (!function_exists('get_site_option')) {
    function get_site_option($option, $default = false) {}
}

if (!function_exists('update_site_option')) {
    function update_site_option($option, $value): bool { return true; }
}

if (!function_exists('wp_salt')) {
    function wp_salt($scheme = 'auth'): string { return ''; }
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data, $flags = 0): int|false { return false; }
}

if (!function_exists('wp_timezone_string')) {
    function wp_timezone_string(): string {
        return 'UTC';
    }
}

if (!function_exists('wp_get_theme')) {
    function wp_get_theme($stylesheet = '', $theme_root = '') {
        return new class {
            public function get($key, $default = '') {
                return $default ?? '';
            }
            public function exists() {
                return true;
            }
        };
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = ''): string {
        return '';
    }
}

if (!function_exists('number_format_i18n')) {
    function number_format_i18n($number, $decimals = 0): string {
        return number_format($number, $decimals);
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($target, $mode = 0777): bool {
        return mkdir($target, $mode, true);
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir($time = null) {
        return [
            'path' => '/wp-content/uploads',
            'url' => 'http://example.com/wp-content/uploads',
            'subdir' => '',
            'basedir' => '/wp-content/uploads',
            'baseurl' => 'http://example.com/wp-content/uploads',
            'error' => false
        ];
    }
}

if (!function_exists('current_action')) {
    function current_action(): string {
        return '';
    }
}

if (!function_exists('current_filter')) {
    function current_filter(): string {
        return '';
    }
}

if (!function_exists('wp_get_environment_type')) {
    function wp_get_environment_type(): string {
        return 'production';
    }
}

// ============================================================================
// WORDPRESS TRANSLATION/LOCALIZATION FUNCTIONS
// ============================================================================

if (!function_exists('__')) {
    function __($text, $domain = 'default'): string {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default'): void {
        echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

if (!function_exists('_n')) {
    function _n($singular, $plural, $count, $domain = 'default'): string {
        return $count === 1 ? $singular : $plural;
    }
}

if (!function_exists('_x')) {
    function _x($text, $context, $domain = 'default'): string {
        return $text;
    }
}

if (!function_exists('_ex')) {
    function _ex($text, $context, $domain = 'default'): void {
        echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

if (!function_exists('_nx')) {
    function _nx($singular, $plural, $count, $context, $domain = 'default'): string {
        return $count === 1 ? $singular : $plural;
    }
}

if (!function_exists('get_locale')) {
    function get_locale(): string {
        return 'en_US';
    }
}

if (!function_exists('load_textdomain')) {
    function load_textdomain($domain, $mofile): bool {
        return false;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $plugin_rel_path = false, $plugin_path = ''): bool {
        return false;
    }
}

// ============================================================================
// DOMPDF CLASSES
// ============================================================================

if (!class_exists('\Dompdf\Dompdf')) {
        class Dompdf {
        public function __construct(array $options = []) {}
        public function loadHtml($html, $encoding = null) {}
        public function loadHtmlFile($filename) {}
        public function render() {}
        public function output() {}
        public function stream($filename = 'document.pdf', $options = []) {}
        public function getCanvas() {}
        public function setProtocol($protocol = 'file://') {}
        public function setPaper($paper = 'letter', $orientation = 'portrait') {}
        public function getOptions() {}
        public function setOptions($options) {}
    }
}

if (!class_exists('\Dompdf\Options')) {
    class Options {
        public function __construct(array $options = []) {}
        public function set(string $key, $value) {}
        public function get(string $key) {}
        public function setFontDir($path) {}
        public function setFontCache($path) {}
        public function getChroot() {}
        public function setChroot($chroot) {}
        public function getBasePath() {}
        public function setBasePath($base_path) {}
    }
}
// TCPDF CLASSES
// ============================================================================

if (!class_exists('TCPDF')) {
    /**
     * TCPDF class stub with global namespace support
     * Supports both import as TCPDF and global namespace reference \TCPDF
     * 
     * @method void AddPage(string $orientation = '', string $format = '', bool $keepmargins = false, bool $blank = false)
     * @method void Cell(float $w, float $h = 0, string $txt = '', int|string $border = 0, int $ln = 0, string $align = '', bool $fill = false, string $link = '', int $stretch = 0, bool $ignore_min_height = false, string $calignment = 'T', string $valignment = 'M')
     * @method void MultiCell(float $w, float $h, string $txt = '', int|string $border = 0, string $align = 'J', bool $fill = false, int $ln = 1, string $x = '', string $y = '', bool $reseth = true, int $stretch = 0, bool $ishtml = false, bool $autopadding = true, float $maxh = 0, string $valign = 'T', bool $fitcell = false)
     * @method string Output(string $name = 'doc.pdf', string $dest = 'I')
     * @method void SetFont(string $family, string $style = '', int $size = 0)
     * @method void SetFontSize(float $size)
     * @method void SetTextColor(int $r, int $g = -1, int $b = -1)
     * @method void SetFillColor(int $r, int $g = -1, int $b = -1)
     * @method void SetDrawColor(int $r, int $g = -1, int $b = -1)
     * @method void SetLineWidth(float $width)
     * @method void Image(string $file, float $x = '', float $y = '', float $w = 0, float $h = 0, string $type = '', string $link = '', string $align = '', bool $resize = false, int $dpi = 300, string $palignment = '', bool $ismask = false, bool $imgmask = false, int|string $border = 0, bool|string $fitbox = false, bool $hidden = false, bool $fitonpage = false, bool $alt = false, string $alttext = '')
     * @method void Ln(float $h = '')
     * @method void Write(float $h, string $txt = '', string $link = '')
     * @method void SetMargins(float $left, float $top, float $right = -1)
     * @method void SetXY(float $x, float $y)
     * @method float GetX()
     * @method float GetY()
     * @method void SetCreator(string $creator)
     * @method void SetTitle(string $title)
     * @method void SetSubject(string $subject)
     * @method void SetAuthor(string $author)
     * @method void SetKeywords(string $keywords)
     */
    class TCPDF {
        // Made public for IDE recognition
        public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8') { return; }
        public function AddPage($orientation = '', $format = '', $keepmargins = false, $blank = false) { return; }
        public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calignment = 'T', $valignment = 'M') { return; }
        public function MultiCell($w, $h, $txt = '', $border = 0, $align = 'J', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false) { return; }
        public function Output($name = 'doc.pdf', $dest = 'I') { return ''; }
        public function SetFont($family, $style = '', $size = 0) { return; }
        public function SetFontSize($size) { return; }
        public function SetTextColor($r, $g = -1, $b = -1) { return; }
        public function SetFillColor($r, $g = -1, $b = -1) { return; }
        public function SetDrawColor($r, $g = -1, $b = -1) { return; }
        public function SetLineWidth($width) { return; }
        public function Image($file, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palignment = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $alttext = '') { return; }
        public function Ln($h = '') { return; }
        public function Write($h, $txt = '', $link = '') { return; }
        public function SetMargins($left, $top, $right = -1) { return; }
        public function SetXY($x, $y) { return; }
        public function GetX() { return 0; }
        public function GetY() { return 0; }
        public function SetCreator($creator) { return; }
        public function SetTitle($title) { return; }
        public function SetSubject($subject) { return; }
        public function SetAuthor($author) { return; }
        public function SetKeywords($keywords) { return; }
    }
}

// Ensure TCPDF is available in global namespace for type hints
if (!class_exists('\TCPDF', false)) {
    class_alias('TCPDF', '\TCPDF');
}

// ============================================================================
// PDF BUILDER CUSTOM FUNCTIONS
// ============================================================================

if (!function_exists('pdf_builder_get_option')) {
        /**
         * Get a PDF Builder option value
         * @param string $option Option name
         * @param mixed $default Default value
         * @return mixed
         */
        function pdf_builder_get_option($option, $default = false) {
            return get_option($option, $default);
        }
    }

    if (!function_exists('pdf_builder_update_option')) {
        /**
         * Update a PDF Builder option value
         * @param string $option Option name
         * @param mixed $value Value to set
         * @return bool
         */
        function pdf_builder_update_option($option, $value) {
            return update_option($option, $value);
        }
    }

    if (!function_exists('pdf_builder_translate')) {
        /**
         * Translate a PDF Builder string
         * @param string $string String to translate
         * @param string $domain Translation domain
         * @return string
         */
        function pdf_builder_translate($string, $domain = 'pdf-builder-pro') {
            return __($string, $domain); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain
        }
    }

    if (!function_exists('pdf_builder_verify_nonce')) {
        /**
         * Verify a PDF Builder nonce
         * @param string $nonce Nonce value
         * @param string|int $action Action name
         * @return int|false
         */
        function pdf_builder_verify_nonce($nonce, $action = -1) {
            return wp_verify_nonce($nonce, $action);
        }
    }

    if (!function_exists('pdf_builder_is_license_active')) {
        /**
         * Check if PDF Builder license is active
         * @return bool
         */
        function pdf_builder_is_license_active() {
            $license_key = pdf_builder_get_option('pdf_builder_license_key');
            return !empty($license_key);
        }
    }

    if (!function_exists('pdf_builder_run_migrations')) {
        /**
         * Run PDF Builder database migrations
         * @param string $version The target version
         * @return bool
         */
        function pdf_builder_run_migrations($version) {
            return true;
        }
    }

    if (!function_exists('pdf_builder_config')) {
        /**
         * Get a PDF Builder config value
         * @param string $key Config key
         * @param mixed $default Default value
         * @return mixed
         */
        function pdf_builder_config($key, $default = null) {
            return $default;
        }
    }

// ============================================================================
// CUSTOM CLASSES
// ============================================================================

if (!class_exists('PDF_Builder_Analytics_Manager')) {
    /**
     * Analytics Manager - Stub for IDE
     */
    class PDF_Builder_Analytics_Manager {
        private static $instance = null;
        
        public static function get_instance() {
            return self::$instance ?: (self::$instance = new self());
        }
        
        public function update_realtime_metrics() {}
        public function get_metrics($period = 'day') {}
    }
}

if (!class_exists('PDF_Builder_Logger')) {
    /**
     * Logger class - Stub for IDE
     */
    class PDF_Builder_Logger {
        private static $instance = null;
        
        public static function get_instance() {
            return self::$instance ?: (self::$instance = new self());
        }
        
        public function error($message, $context = []) {}
        public function warning($message, $context = []) {}
        public function info($message, $context = []) {}
        public function critical($message, $context = []) {}
        public function get_error_count($hours = 24) {}
        public function get_warning_count($hours = 24) {}
        public function get_critical_count($hours = 24) {}
        public function get_error_rate() {}
    }
}

if (!class_exists('PDF_Builder_Security_Validator')) {
    /**
     * Security Validator class - Stub for IDE
     */
    class PDF_Builder_Security_Validator {
        private static $instance = null;
        
        public static function get_instance() {
            return self::$instance ?: (self::$instance = new self());
        }
        
        public function validate($data) {}
        public function sanitize($input) {}
        public function verify_permissions($capability) {}
    }
}

if (!class_exists('PDF_Builder_Update_Manager')) {
    /**
     * Update Manager class - Stub for IDE
     */
    class PDF_Builder_Update_Manager {
        private static $instance = null;
        
        public static function get_instance() {
            return self::$instance ?: (self::$instance = new self());
        }
        
        public function check_update() {}
        public function apply_update() {}
        public function get_update_info() {}
    }
}

if (!class_exists('Canvas_Manager', false)) {
    /**
     * Canvas Manager class - Stub for IDE recognition
     * Actual implementation is in plugin/src/Canvas/Canvas_Manager.php
     * 
     * @method array getAllSettings()
     * @method bool save_settings(array $settings)
     * @method bool reset_to_defaults()
     * @method mixed get_setting(string $key, mixed $default = null)
     * @method bool update_setting(string $key, mixed $value)
     * @method static self getInstance()
     * @method static self get_instance()
     */
    class Canvas_Manager {
        private static $instance = null;
        
        public static function getInstance() {
            return self::$instance ?: (self::$instance = new self());
        }
        
        public static function get_instance() {
            return self::getInstance();
        }
        
        public function getAllSettings() { return []; }
        public function save_settings($settings) { return true; }
        public function reset_to_defaults() { return true; }
        public function get_setting($key, $default = null) { return $default; }
        public function update_setting($key, $value) { return true; }
    }
}

if (!class_exists('WP_Error')) {
    /**
     * WordPress Error class
     */
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_codes() {}
        public function get_error_code() {}
        public function get_error_messages($code = '') {}
        public function get_error_message($code = '') {}
        public function get_error_data($code = '') {}
    }
}

if (!class_exists('Exception')) {
    /**
     * PHP Exception class
     */
    class Exception extends \Exception {}
}
