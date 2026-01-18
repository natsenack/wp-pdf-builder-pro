<?php
/**
 * PDF Builder Security Manager
 *
 * Centralizes security-related operations like nonce verification
 */

class PDF_Builder_Security_Manager {

    /**
     * Verify nonce and handle errors
     *
     * @param string $nonce The nonce value to verify
     * @param string $action The action name
     * @return bool True if nonce is valid, false otherwise
     */
    public static function verify_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            // error_log("PDF Builder: Invalid nonce for action '{$action}'");
            return false;
        }
        return true;
    }

    /**
     * Verify nonce and die with error if invalid
     *
     * @param string $nonce The nonce value to verify
     * @param string $action The action name
     */
    public static function verify_nonce_or_die($nonce, $action) {
        if (!self::verify_nonce($nonce, $action)) {
            wp_die(__('Security check failed', 'pdf-builder-pro'), __('Error', 'pdf-builder-pro'), array('response' => 403));
        }
    }

    /**
     * Verify nonce against multiple possible actions
     *
     * @param string $nonce The nonce value to verify
     * @param array $actions Array of action names to check against
     * @return bool True if nonce is valid for any action, false otherwise
     */
    public static function verify_nonce_multiple($nonce, $actions) {
        foreach ($actions as $action) {
            if (self::verify_nonce($nonce, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create nonce
     *
     * @param string $action The action name
     * @return string The nonce
     */
    public static function create_nonce($action) {
        return wp_create_nonce($action);
    }

    /**
     * Check if PHP error debugging is enabled
     *
     * @return bool True if PHP error debugging is enabled
     */
    public static function is_php_debug_enabled() {
        return pdf_builder_get_option('pdf_builder_debug_php_errors', false);
    }

    /**
     * Check if database debugging is enabled
     *
     * @return bool True if database debugging is enabled
     */
    public static function is_database_debug_enabled() {
        return pdf_builder_get_option('pdf_builder_debug_database', false);
    }

    /**
     * Check if performance debugging is enabled
     *
     * @return bool True if performance debugging is enabled
     */
    public static function is_performance_debug_enabled() {
        return pdf_builder_get_option('pdf_builder_debug_performance', false);
    }

    /**
     * Conditional PHP debug logging
     *
     * @param string $type Debug type ('php_errors', 'database', 'performance')
     * @param mixed ...$args Arguments to log
     */
    public static function debug_log($type, ...$args) {
        $enabled = false;

        switch ($type) {
            case 'php_errors':
                $enabled = self::is_php_debug_enabled();
                break;
            case 'database':
                $enabled = self::is_database_debug_enabled();
                break;
            case 'performance':
                $enabled = self::is_performance_debug_enabled();
                break;
        }

        if ($enabled) {
            $prefix = '[PDF Builder Debug ' . ucfirst($type) . ']';
            $message = $prefix . ' ' . implode(' ', array_map(function($arg) {
                return is_string($arg) ? $arg : print_r($arg, true);
            }, $args));

            // error_log($message);
        }
    }
}


