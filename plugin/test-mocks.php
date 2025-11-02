<?php
/**
 * Mocks pour les tests PDF Builder Pro
 * Simule les fonctions WordPress nécessaires
 */

// Empêcher l'accès direct
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Accès direct interdit');
}

// Simuler les fonctions WordPress de base
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return md5($action . 'test_nonce_salt');
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return $nonce === wp_create_nonce($action);
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Simuler un utilisateur admin
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        throw new Exception($message ?: 'wp_die called');
    }
}

if (!function_exists('wp_send_json')) {
    function wp_send_json($response) {
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($error) {
        wp_send_json(['success' => false, 'data' => $error]);
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        wp_send_json(['success' => true, 'data' => $data]);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($content) {
        return $content; // Simplifié pour les tests
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        static $options = [
            'wp_pdf_builder_pro_cache_enabled' => '1',
            'wp_pdf_builder_pro_cache_ttl' => '3600',
            'wp_pdf_builder_pro_rate_limit' => '10',
            'wp_pdf_builder_pro_max_file_size' => '10485760'
        ];
        return $options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Simuler l'ajout d'action
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Simuler l'ajout de filtre
        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        // Simuler l'exécution d'action
        return true;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) {
        return $value;
    }
}

// Simuler les fonctions WooCommerce
if (!function_exists('WC')) {
    function WC() {
        return (object)[
            'countries' => (object)[
                'countries' => [
                    'FR' => 'France',
                    'US' => 'United States',
                    'GB' => 'United Kingdom'
                ]
            ]
        ];
    }
}

if (!function_exists('get_woocommerce_currency_symbol')) {
    function get_woocommerce_currency_symbol($currency = '') {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£'
        ];
        return $symbols[$currency] ?? '€';
    }
}

if (!function_exists('get_woocommerce_currency')) {
    function get_woocommerce_currency() {
        return 'EUR';
    }
}

if (!function_exists('wc_price')) {
    function wc_price($price, $args = []) {
        $currency = $args['currency'] ?? 'EUR';
        return number_format($price, 2, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        // Retourner un mock order pour les tests
        return new class($order_id) {
            private $id;
            public function __construct($id) { $this->id = $id; }
            public function get_id() { return $this->id; }
            public function get_order_number() { return '#' . $this->id; }
            public function get_total() { return 99.99; }
            public function get_status() { return 'completed'; }
            public function get_formatted_billing_full_name() { return 'Test Customer'; }
        };
    }
}

// Simuler les fonctions de cache
if (!function_exists('wp_cache_get')) {
    function wp_cache_get($key, $group = '') {
        return false; // Pas de cache en test
    }
}

if (!function_exists('wp_cache_set')) {
    function wp_cache_set($key, $data, $group = '', $expire = 0) {
        return true;
    }
}

// Simuler les fonctions de fichiers
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'path' => sys_get_temp_dir() . '/wp-uploads',
            'url' => 'http://test.com/wp-content/uploads',
            'subdir' => '',
            'basedir' => sys_get_temp_dir() . '/wp-uploads',
            'baseurl' => 'http://test.com/wp-content/uploads',
            'error' => false
        ];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) {
        return mkdir($dir, 0755, true);
    }
}

// Simuler les fonctions de logging
if (!function_exists('error_log')) {
    function error_log($message, $type = 0, $destination = null, $extra_headers = null) {
        // Enregistrer dans un fichier temporaire pour les tests
        $log_file = sys_get_temp_dir() . '/test-error.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . ' ' . $message . "\n", FILE_APPEND);
        return true;
    }
}

// Simuler les fonctions de performance
if (!function_exists('microtime')) {
    function microtime($get_as_float = false) {
        return $get_as_float ? microtime(true) : '0.123456 1234567890';
    }
}

// Simuler les fonctions de sécurité
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// Simuler les fonctions de base de données
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key, $single = false) {
        // Retourner des données fictives pour les tests
        $meta = [
            '_wp_pdf_template_data' => json_encode(['template' => ['elements' => []]]),
            '_wp_pdf_order_id' => '12345'
        ];
        return $single ? ($meta[$key] ?? '') : [$meta[$key] ?? ''];
    }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $key, $value) {
        return true;
    }
}

// Simuler les fonctions d'utilisateur
if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1; // Admin user
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object)[
            'ID' => 1,
            'user_login' => 'admin',
            'user_email' => 'admin@test.com',
            'roles' => ['administrator']
        ];
    }
}

// Simuler les fonctions de session
if (!function_exists('session_start')) {
    function session_start() {
        return true;
    }
}

if (!function_exists('session_id')) {
    function session_id($id = null) {
        static $session_id = 'test_session_123';
        if ($id !== null) {
            $session_id = $id;
        }
        return $session_id;
    }
}

// Simuler les fonctions de requête HTTP
if (!function_exists('wp_remote_get')) {
    function wp_remote_get($url, $args = []) {
        return [
            'body' => 'Mock response',
            'response' => ['code' => 200],
            'headers' => []
        ];
    }
}

if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args = []) {
        return [
            'body' => 'Mock response',
            'response' => ['code' => 200],
            'headers' => []
        ];
    }
}

// Simuler les fonctions de date
if (!function_exists('current_time')) {
    function current_time($type, $gmt = false) {
        return date($type === 'timestamp' ? 'U' : 'Y-m-d H:i:s');
    }
}

if (!function_exists('date_i18n')) {
    function date_i18n($format, $timestamp = false) {
        return date($format, $timestamp ?: time());
    }
}

// Simuler les fonctions de formatage
if (!function_exists('number_format_i18n')) {
    function number_format_i18n($number, $decimals = 0) {
        return number_format($number, $decimals, ',', ' ');
    }
}

// Simuler les fonctions de traduction
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

// Simuler les fonctions de plugin
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://test.com/wp-content/plugins/pdf-builder-pro/';
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return 'http://test.com/wp-content/plugins/pdf-builder-pro/' . $path;
    }
}

// Simuler les fonctions AJAX
if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action, $query_arg = false, $die = true) {
        return true;
    }
}

if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action = -1, $query_arg = '_wpnonce', $die = true) {
        return true;
    }
}

// Simuler les fonctions de contexte
if (!function_exists('is_admin')) {
    function is_admin() {
        return defined('WP_ADMIN') && WP_ADMIN;
    }
}

if (!function_exists('is_ajax')) {
    function is_ajax() {
        return defined('DOING_AJAX') && DOING_AJAX;
    }
}

// Simuler les fonctions de développement
if (!function_exists('wp_debug_mode')) {
    function wp_debug_mode() {
        return WP_DEBUG;
    }
}

// Simuler les fonctions de performance
if (!function_exists('wp_using_ext_object_cache')) {
    function wp_using_ext_object_cache($using = null) {
        return false;
    }
}

// Simuler les fonctions de fichiers temporaires
if (!function_exists('wp_tempnam')) {
    function wp_tempnam($filename = '', $dir = '') {
        $dir = $dir ?: sys_get_temp_dir();
        return tempnam($dir, $filename ?: 'wp_temp_');
    }
}

// Simuler les fonctions de nettoyage
if (!function_exists('wp_delete_file')) {
    function wp_delete_file($file) {
        return unlink($file);
    }
}

// Simuler les fonctions de tâches planifiées
if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($hook, $args = []) {
        return false; // Pas de tâche planifiée en test
    }
}

if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($timestamp, $recurrence, $hook, $args = []) {
        return true;
    }
}

if (!function_exists('wp_unschedule_event')) {
    function wp_unschedule_event($timestamp, $hook, $args = []) {
        return true;
    }
}

if (!function_exists('wp_clear_scheduled_hook')) {
    function wp_clear_scheduled_hook($hook, $args = []) {
        return true;
    }
}

echo "✅ Mocks WordPress chargés pour les tests\n";