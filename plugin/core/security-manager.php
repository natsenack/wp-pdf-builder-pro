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
            error_log("PDF Builder: Invalid nonce for action '{$action}'");
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
}