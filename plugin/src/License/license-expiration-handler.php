<?php

/**
 * License Expiration Handler
 * Handles automatic license expiration checking via WordPress cron
 */

namespace PDFBuilderPro\License;

class License_Expiration_Handler
{
    /**
     * Initialize the expiration handler
     */
    public static function init()
    {
        // Schedule daily expiration check
        \add_action('init', [__CLASS__, 'scheduleExpirationCheck']);
        \add_action('pdf_builder_checkLicenseExpiration', [__CLASS__, 'checkLicenseExpiration']);
    }

    /**
     * Schedule the daily expiration check
     */
    public static function scheduleExpirationCheck()
    {
        if (!\wp_next_scheduled('pdf_builder_checkLicenseExpiration')) {
            \wp_schedule_event(\current_time('timestamp'), 'daily', 'pdf_builder_checkLicenseExpiration');
        }
    }

    /**
     * Check if licenses have expired and update their status
     */
    public static function checkLicenseExpiration()
    {
        // Log the start of the check
        error_log('[PDF Builder] checkLicenseExpiration: Début de la vérification');

        // Check premium license expiration
        $license_expires = pdf_builder_get_option('pdf_builder_license_expires', '');
        $license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
        error_log('[PDF Builder] checkLicenseExpiration: License expires=' . $license_expires . ', status=' . $license_status);
        if (!empty($license_expires) && $license_status !== 'free') {
            $expires_date = new \DateTime($license_expires);
            $now = new \DateTime();
        // If license has expired, update status
            if ($now > $expires_date) {
                error_log('[PDF Builder] checkLicenseExpiration: Licence premium expirée, suppression de la clé');
                pdf_builder_update_option('pdf_builder_license_status', 'expired');
                pdf_builder_delete_option('pdf_builder_license_key');
// Clear the key

                // Log the expiration
                
            } else {
        // Check if we should send a notification (30 days or 7 days before expiration)
                $diff_days = $expires_date->diff($now)->days;
        // Send notification if 30 days or 7 days remaining
                if (in_array($diff_days, [30, 7])) {
                    self::sendExpirationNotification($license_expires, $diff_days);
                }
            }
        }

        // Check test key expiration
        $test_key_expires = pdf_builder_get_option('pdf_builder_license_test_key_expires', '');
        $test_key = pdf_builder_get_option('pdf_builder_license_test_key', '');
        error_log('[PDF Builder] checkLicenseExpiration: Test key expires=' . $test_key_expires . ', test key=' . substr($test_key, 0, 10) . '...');
        if (!empty($test_key_expires) && !empty($test_key)) {
            // Convertir les dates au format Y-m-d pour comparaison
            $expires_date = new \DateTime($test_key_expires . ' 23:59:59'); // Fin de journée
            $now = new \DateTime();
        // If test key has expired, remove it
            if ($now > $expires_date) {
                error_log('[PDF Builder] checkLicenseExpiration: Clé de test expirée, suppression');
                pdf_builder_delete_option('pdf_builder_license_test_key');
                pdf_builder_delete_option('pdf_builder_license_test_key_expires');
                pdf_builder_delete_option('pdf_builder_license_test_mode_enabled');
                pdf_builder_update_option('pdf_builder_license_status', 'free');
// Log the expiration
                
            }
        }

        error_log('[PDF Builder] checkLicenseExpiration: Fin de la vérification');
    }

    /**
     * Record a logged event for upcoming license expiration
     */
    private static function sendExpirationNotification($expiration_date, $days_remaining)
    {
        // Legacy notification emails are disabled: record the event in logs/DB for audit purposes
        $site_name = \get_option('blogname');
        $site_url = \get_option('siteurl');
        error_log('License nearing expiration: ' . $site_name . ' (remaining ' . $days_remaining . ' days) - expires: ' . $expiration_date);

        // Check if we already recorded an event for this date today
        $last_notification = pdf_builder_get_option('pdf_builder_license_last_notification_' . date('Y-m-d'), '');
        if (!empty($last_notification)) {
            return; // event already recorded today
        }

        // Record that we've logged this event today
        pdf_builder_update_option('pdf_builder_license_last_notification_' . date('Y-m-d'), time());
    }

    /**
     * Clear the scheduled expiration check (call on plugin deactivation)
     */
    public static function clearScheduledExpirationCheck()
    {
        $timestamp = \wp_next_scheduled('pdf_builder_checkLicenseExpiration');
        if ($timestamp) {
            \wp_unschedule_event($timestamp, 'pdf_builder_checkLicenseExpiration');
        }
    }

    /**
     * Alias for clearScheduledExpirationCheck (backward compatibility)
     */
    public static function clear_scheduled_expiration_check()
    {
        self::clearScheduledExpirationCheck();
    }
}




