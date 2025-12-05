<?php
/**
 * PDF Builder Pro - Settings Helpers
 * Fonctions utilitaires pour la gestion des paramètres
 */

/**
 * Sauvegarde les rôles autorisés
 */
function pdf_builder_save_allowed_roles($roles) {
    // // // error_log('[SAVE ROLES] ===== DÉBUT SAUVEGARDE RÔLES =====');
    // // // error_log('[SAVE ROLES] Raw input: ' . print_r($roles, true));
    // // // error_log('[SAVE ROLES] Type of input: ' . gettype($roles));
    
    // Unslash the input first (WordPress slashes POST data)
    $roles = wp_unslash($roles);
    // // // error_log('[SAVE ROLES] After wp_unslash: ' . print_r($roles, true));
    
    // Décoder le JSON si c'est une string JSON
    if (is_string($roles) && (strpos($roles, '[') === 0 || strpos($roles, '{') === 0)) {
        // // // error_log('[SAVE ROLES] Input is JSON string, decoding...');
        $decoded = json_decode($roles, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $roles = $decoded;
            // // // error_log('[SAVE ROLES] JSON decoded successfully: ' . print_r($roles, true));
        } else {
            // // // error_log('[SAVE ROLES] JSON decode error: ' . json_last_error_msg());
        }
    }

    if (!class_exists('PDF_Builder\\Security\\Role_Manager')) {
        // // // error_log('[SAVE ROLES] Role_Manager class not available, using fallback');
        // Fallback si Role_Manager n'est pas disponible
        if (!is_array($roles)) {
            // // // error_log('[SAVE ROLES] Roles is not array, converting to empty array');
            $roles = [];
        }

        // Nettoyer et valider les rôles
        $valid_roles = [];
        global $wp_roles;
        $all_roles = array_keys($wp_roles->roles);
        // // // error_log('[SAVE ROLES] Available WordPress roles: ' . print_r($all_roles, true));

        foreach ($roles as $role) {
            // // // error_log('[SAVE ROLES] Checking role: ' . $role);
            if (in_array($role, $all_roles)) {
                $valid_roles[] = $role;
                // // // error_log('[SAVE ROLES] Role valid: ' . $role);
            } else {
                // // // error_log('[SAVE ROLES] Role invalid: ' . $role);
            }
        }

        // Toujours inclure administrator
        if (!in_array('administrator', $valid_roles)) {
            $valid_roles[] = 'administrator';
            // // // error_log('[SAVE ROLES] Added administrator role');
        }

        // // // error_log('[SAVE ROLES] Final valid roles: ' . print_r($valid_roles, true));
        
        $settings = get_option('pdf_builder_settings', []);
        // // // error_log('[SAVE ROLES] Current settings before save: ' . print_r($settings, true));
        
        $settings['pdf_builder_allowed_roles'] = $valid_roles;
        update_option('pdf_builder_settings', $settings);
        
        // // // error_log('[SAVE ROLES] Settings after save: ' . print_r($settings, true));
        // // // error_log('[SAVE ROLES] ===== FIN SAUVEGARDE RÔLES =====');

        return $valid_roles;
    }

    // Utiliser le Role_Manager si disponible - COMMENTÉ POUR CONCORDANCE AVEC GET_ALLOWED_ROLES
    /*
    // // // error_log('[SAVE ROLES] Using Role_Manager class');
    \PDF_Builder\Security\Role_Manager::setAllowedRoles($roles);
    $result = \PDF_Builder\Security\Role_Manager::getAllowedRoles();
    // // // error_log('[SAVE ROLES] Role_Manager result: ' . print_r($result, true));
    // // // error_log('[SAVE ROLES] ===== FIN SAUVEGARDE RÔLES =====');
    return $result;
    */

    // TOUJOURS UTILISER LE FALLBACK POUR CONCORDANCE AVEC pdf_builder_get_allowed_roles
    // // // error_log('[SAVE ROLES] Using fallback for consistency with get_allowed_roles');
    if (!is_array($roles)) {
        // // // error_log('[SAVE ROLES] Roles is not array, converting to empty array');
        $roles = [];
    }

    // Nettoyer et valider les rôles
    $valid_roles = [];
    global $wp_roles;
    $all_roles = array_keys($wp_roles->roles);
    // // // error_log('[SAVE ROLES] Available WordPress roles: ' . print_r($all_roles, true));

    foreach ($roles as $role) {
        // // // error_log('[SAVE ROLES] Checking role: ' . $role);
        if (in_array($role, $all_roles)) {
            $valid_roles[] = $role;
            // // // error_log('[SAVE ROLES] Role valid: ' . $role);
        } else {
            // // // error_log('[SAVE ROLES] Role invalid: ' . $role);
        }
    }

    // Toujours inclure administrator
    if (!in_array('administrator', $valid_roles)) {
        $valid_roles[] = 'administrator';
        // // // error_log('[SAVE ROLES] Added administrator role');
    }

    // // // error_log('[SAVE ROLES] Final valid roles: ' . print_r($valid_roles, true));
    
    $settings = get_option('pdf_builder_settings', []);
    // // // error_log('[SAVE ROLES] Current settings before save: ' . print_r($settings, true));
    
    $settings['pdf_builder_allowed_roles'] = $valid_roles;
    update_option('pdf_builder_settings', $settings);
    
    // // // error_log('[SAVE ROLES] Settings after save: ' . print_r($settings, true));
    // // // error_log('[SAVE ROLES] ===== FIN SAUVEGARDE RÔLES =====');

    return $valid_roles;
}

/**
 * Récupère les rôles autorisés
 */
function pdf_builder_get_allowed_roles() {
    // Simplified version - always use fallback to avoid class loading issues
    $settings = get_option('pdf_builder_settings', []);
    $roles = $settings['pdf_builder_allowed_roles'] ?? null;

    // DEBUG: Log what we're getting
    error_log('[GET ROLES] Settings: ' . json_encode($settings));
    error_log('[GET ROLES] Roles from settings: ' . json_encode($roles));
    error_log('[GET ROLES] Is array: ' . (is_array($roles) ? 'yes' : 'no'));
    error_log('[GET ROLES] Is empty: ' . (empty($roles) ? 'yes' : 'no'));

    if (!is_array($roles) || empty($roles)) {
        // Valeurs par défaut
        $default_roles = ['administrator', 'editor', 'shop_manager'];
        error_log('[GET ROLES] Using defaults: ' . json_encode($default_roles));
        return $default_roles;
    }

    // Toujours inclure administrator
    if (!in_array('administrator', $roles)) {
        $roles[] = 'administrator';
        error_log('[GET ROLES] Added administrator, final roles: ' . json_encode($roles));
    }

    $final_roles = array_unique($roles);
    error_log('[GET ROLES] Final roles: ' . json_encode($final_roles));
    return $final_roles;
}

/**
 * Vérifie si un rôle est autorisé
 */
function pdf_builder_is_role_allowed($role) {
    $allowed_roles = pdf_builder_get_allowed_roles();
    return in_array($role, $allowed_roles);
}

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
// DEBUG HELPERS (Development only)
// =============================================================================

if (!function_exists('pdf_builder_debug_log')) {
    /**
     * Debug logging helper
     */
    function pdf_builder_debug_log($message, $level = 'info') {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $log_message = sprintf(
            '[PDF Builder Pro] [%s] %s',
            strtoupper($level),
            is_string($message) ? $message : json_encode($message)
        );

        if (function_exists('error_log')) {
            // // // error_log($log_message);
        }
    }
}

if (!function_exists('pdf_builder_is_development')) {
    /**
     * Check if we're in development mode
     */
    function pdf_builder_is_development() {
        return (defined('WP_DEBUG') && WP_DEBUG) ||
               (defined('PDF_BUILDER_DEVELOPMENT') && PDF_BUILDER_DEVELOPMENT);
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
    $settings = get_option('pdf_builder_settings', array());
}

// Only run this code when not included in AJAX context
if (!defined('DOING_AJAX') || !DOING_AJAX) {
    // Ensure nonce is available
    global $nonce;
    if (!isset($nonce)) {
        $nonce = wp_create_nonce('pdf_builder_ajax');
    }
}
