<?php
/**
 * License Expiration Handler
 * Handles automatic license expiration checking via WordPress cron
 */

namespace PDFBuilderPro\License;

class License_Expiration_Handler {
    
    /**
     * Initialize the expiration handler
     */
    public static function init() {
        // Schedule daily expiration check
        add_action('init', [__CLASS__, 'schedule_expiration_check']);
        add_action('pdf_builder_check_license_expiration', [__CLASS__, 'check_license_expiration']);
    }
    
    /**
     * Schedule the daily expiration check
     */
    public static function schedule_expiration_check() {
        if (!wp_next_scheduled('pdf_builder_check_license_expiration')) {
            wp_schedule_event(current_time('timestamp'), 'daily', 'pdf_builder_check_license_expiration');
        }
    }
    
    /**
     * Check if licenses have expired and update their status
     */
    public static function check_license_expiration() {
        // Check premium license expiration
        $license_expires = get_option('pdf_builder_license_expires', '');
        $license_status = get_option('pdf_builder_license_status', 'free');
        
        if (!empty($license_expires) && $license_status !== 'free') {
            $expires_date = new \DateTime($license_expires);
            $now = new \DateTime();
            
            // If license has expired, update status
            if ($now > $expires_date) {
                update_option('pdf_builder_license_status', 'expired');
                update_option('pdf_builder_license_key', ''); // Clear the key
                
                // Log the expiration
                error_log('PDF Builder Pro: License expired on ' . $license_expires);
            }
        }
        
        // Check test key expiration
        $test_key_expires = get_option('pdf_builder_license_test_key_expires', '');
        $test_key = get_option('pdf_builder_license_test_key', '');
        
        if (!empty($test_key_expires) && !empty($test_key)) {
            $expires_date = new \DateTime($test_key_expires);
            $now = new \DateTime();
            
            // If test key has expired, remove it
            if ($now > $expires_date) {
                delete_option('pdf_builder_license_test_key');
                delete_option('pdf_builder_license_test_key_expires');
                delete_option('pdf_builder_license_test_mode_enabled');
                update_option('pdf_builder_license_status', 'free');
                
                // Log the expiration
                error_log('PDF Builder Pro: Test key expired on ' . $test_key_expires);
            }
        }
    }
    
    /**
     * Clear the scheduled expiration check (call on plugin deactivation)
     */
    public static function clear_scheduled_expiration_check() {
        $timestamp = wp_next_scheduled('pdf_builder_check_license_expiration');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'pdf_builder_check_license_expiration');
        }
    }
}
