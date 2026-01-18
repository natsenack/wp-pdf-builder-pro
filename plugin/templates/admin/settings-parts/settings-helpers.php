<?php
/**
 * PDF Builder Pro - Settings Helper Functions
 * Common utility functions used across all settings tabs
 * Updated: 2025-12-03
 */

// Prevent direct access
if (!defined('ABSPATH')) exit('No direct access');

// =============================================================================
// SAFE WORDPRESS FUNCTION WRAPPERS
// =============================================================================

if (!function_exists('pdf_builder_safe_get_option')) {
    /**
     * Safe wrapper for get_option that works even when WordPress is not fully loaded
     */
    function pdf_builder_safe_get_option($option, $default = '') {
        if (function_exists('get_option')) {
            return get_option($option, $default);
        }
        return $default;
    }
}

if (!function_exists('pdf_builder_safe_checked')) {
    /**
     * Safe wrapper for checked function
     */
    function pdf_builder_safe_checked($checked, $current = true, $echo = true) {
        if (function_exists('checked')) {
            return checked($checked, $current, $echo);
        }
        $result = checked($checked, $current, false);
        if ($echo) echo $result;
        return $result;
    }
}

if (!function_exists('pdf_builder_safe_selected')) {
    /**
     * Safe wrapper for selected function
     */
    function pdf_builder_safe_selected($selected, $current = true, $echo = true) {
        if (function_exists('selected')) {
            return selected($selected, $current, $echo);
        }
        $result = selected($selected, $current, false);
        if ($echo) echo $result;
        return $result;
    }
}

if (!function_exists('pdf_builder_safe_esc_attr')) {
    /**
     * Safe wrapper for esc_attr function
     */
    function pdf_builder_safe_esc_attr($text) {
        if (function_exists('esc_attr')) {
            return esc_attr($text);
        }
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pdf_builder_safe_get_bloginfo')) {
    /**
     * Safe wrapper for get_bloginfo function
     */
    function pdf_builder_safe_get_bloginfo($show = '') {
        if (function_exists('get_bloginfo')) {
            return get_bloginfo($show);
        }
        return '';
    }
}

if (!function_exists('pdf_builder_safe_esc_html')) {
    /**
     * Safe wrapper for esc_html function
     */
    function pdf_builder_safe_esc_html($text) {
        if (function_exists('esc_html')) {
            return esc_html($text);
        }
        return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
    }
}

if (!function_exists('pdf_builder_safe_wp_nonce_field')) {
    /**
     * Safe wrapper for wp_nonce_field function
     */
    function pdf_builder_safe_wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        if (function_exists('wp_nonce_field')) {
            return wp_nonce_field($action, $name, $referer, $echo);
        }
        return '';
    }
}

if (!function_exists('pdf_builder_safe_wp_create_nonce')) {
    /**
     * Safe wrapper for wp_create_nonce function
     */
    function pdf_builder_safe_wp_create_nonce($action = -1) {
        if (function_exists('wp_create_nonce')) {
            return wp_create_nonce($action);
        }
        return '';
    }
}

// =============================================================================
// SETTINGS ARRAY ACCESS HELPERS
// =============================================================================

if (!function_exists('pdf_builder_get_setting')) {
    /**
     * Get a setting value from the global settings array
     */
    function pdf_builder_get_setting($key, $default = '') {
        global $settings;
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
}

if (!function_exists('pdf_builder_setting_checked')) {
    /**
     * Output checked attribute for a setting checkbox
     */
    function pdf_builder_setting_checked($key, $current = true, $echo = true) {
        global $settings;
        $value = isset($settings[$key]) ? $settings[$key] : false;
        return pdf_builder_safe_checked($value, $current, $echo);
    }
}

if (!function_exists('pdf_builder_setting_selected')) {
    /**
     * Output selected attribute for a setting select option
     */
    function pdf_builder_setting_selected($key, $current = true, $echo = true) {
        global $settings;
        $value = isset($settings[$key]) ? $settings[$key] : '';
        return pdf_builder_safe_selected($value, $current, $echo);
    }
}

// =============================================================================
// FORM VALIDATION HELPERS
// =============================================================================

if (!function_exists('pdf_builder_sanitize_text')) {
    /**
     * Sanitize text input
     */
    function pdf_builder_sanitize_text($input) {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($input);
        }
        return strip_tags(trim($input));
    }
}

if (!function_exists('pdf_builder_sanitize_email')) {
    /**
     * Sanitize email input
     */
    function pdf_builder_sanitize_email($input) {
        if (function_exists('sanitize_email')) {
            return sanitize_email($input);
        }
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('pdf_builder_validate_boolean')) {
    /**
     * Validate and convert to boolean
     */
    function pdf_builder_validate_boolean($input) {
        return in_array($input, ['1', 'true', 'on', 'yes', true], true);
    }
}

// =============================================================================
// WORDPRESS CAPABILITY HELPERS
// =============================================================================

if (!function_exists('pdf_builder_current_user_can')) {
    /**
     * Safe wrapper for current_user_can
     */
    function pdf_builder_current_user_can($capability) {
        if (function_exists('current_user_can')) {
            return current_user_can($capability);
        }
        return false;
    }
}

if (!function_exists('pdf_builder_user_can_manage_options')) {
    /**
     * Check if current user can manage options
     */
    function pdf_builder_user_can_manage_options() {
        return pdf_builder_current_user_can('manage_options');
    }
}

// =============================================================================
// FILE SYSTEM HELPERS
// =============================================================================

if (!function_exists('pdf_builder_get_upload_dir')) {
    /**
     * Get WordPress upload directory for PDF Builder
     */
    function pdf_builder_get_upload_dir() {
        if (function_exists('wp_upload_dir')) {
            $upload_dir = wp_upload_dir();
            return $upload_dir['basedir'] . '/pdf-builder-pro';
        }
        return WP_CONTENT_DIR . '/uploads/pdf-builder-pro';
    }
}

if (!function_exists('pdf_builder_ensure_upload_dir')) {
    /**
     * Ensure upload directory exists and is writable
     */
    function pdf_builder_ensure_upload_dir() {
        $upload_dir = pdf_builder_get_upload_dir();

        if (!file_exists($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }

        return is_writable($upload_dir);
    }
}

// =============================================================================
// INITIALIZATION
// =============================================================================

// Ensure global settings variable is available
global $settings;
if (!isset($settings)) {
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
}

// Ensure nonce is available
global $nonce;
if (!isset($nonce)) {
    $nonce = wp_create_nonce('pdf_builder_ajax');
}



