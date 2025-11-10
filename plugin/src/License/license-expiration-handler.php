<?php

/**
 * License Expiration Handler
 * Handles automatic license expiration checking via WordPress cron
 */

namespace PDFBuilderPro\License;

class LicenseExpirationHandler
{
    /**
     * Initialize the expiration handler
     */
    public static function init()
    {
        // Schedule daily expiration check
        add_action('init', [__CLASS__, 'scheduleExpirationCheck']);
        add_action('pdf_builder_checkLicenseExpiration', [__CLASS__, 'checkLicenseExpiration']);
    }

    /**
     * Schedule the daily expiration check
     */
    public static function scheduleExpirationCheck()
    {
        if (!wp_next_scheduled('pdf_builder_checkLicenseExpiration')) {
            wp_schedule_event(current_time('timestamp'), 'daily', 'pdf_builder_checkLicenseExpiration');
        }
    }

    /**
     * Check if licenses have expired and update their status
     */
    public static function checkLicenseExpiration()
    {
        // Check premium license expiration
        $license_expires = get_option('pdf_builder_license_expires', '');
        $license_status = get_option('pdf_builder_license_status', 'free');
        if (!empty($license_expires) && $license_status !== 'free') {
            $expires_date = new \DateTime($license_expires);
            $now = new \DateTime();
        // If license has expired, update status
            if ($now > $expires_date) {
                update_option('pdf_builder_license_status', 'expired');
                update_option('pdf_builder_license_key', '');
// Clear the key

                // Log the expiration
                error_log('PDF Builder Pro: License expired on ' . $license_expires);
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
     * Send email notification for upcoming license expiration
     */
    private static function sendExpirationNotification($expiration_date, $days_remaining)
    {
        // Check if notifications are enabled
        $enable_notifications = get_option('pdf_builder_license_enable_notifications', true);
        if (!$enable_notifications) {
            return;
        }

        // Get notification email
        $notification_email = get_option('pdf_builder_license_notification_email', get_option('admin_email'));
        if (empty($notification_email)) {
            return;
        }

        // Check if we already sent a notification for this date today
        $last_notification = get_option('pdf_builder_license_last_notification_' . date('Y-m-d'), '');
        if (!empty($last_notification)) {
            return;
        // Already sent today
        }

        // Prepare email
        $site_name = get_option('blogname');
        $site_url = get_option('siteurl');

        $subject = sprintf(
            '[%s] Votre licence PDF Builder Pro expire dans %d jour%s',
            $site_name,
            $days_remaining,
            $days_remaining > 1 ? 's' : ''
        );
        $message = sprintf('<h2>Notification d\'expiration de licence</h2>' .
            '<p>Bonjour,</p>' .
            '<p>Votre licence PDF Builder Pro pour <strong>%s</strong> expire dans <strong>%d jour%s</strong> (le %s).</p>' .
            '<p>Nous vous recommandons de renouveler votre licence dès maintenant pour continuer à bénéficier de toutes les mises à jour et du support.</p>' .
            '<hr>' .
            '<p><strong>Détails :</strong></p>' .
            '<ul>' .
            '<li>Site : %s</li>' .
            '<li>Date d\'expiration : %s</li>' .
            '<li>Jours restants : %d</li>' .
            '</ul>' .
            '<hr>' .
            '<p><a href="%s">Renouveler votre licence</a></p>' .
            '<p><em>Vous recevez ce email car vous êtes administrateur de ce site. ' .
            'Vous pouvez désactiver les notifications dans l\'onglet Licence des paramètres de PDF Builder Pro.</em></p>', $site_name, $days_remaining, $days_remaining > 1 ? 's' : '', date('d/m/Y', strtotime($expiration_date)), $site_url, date('d/m/Y', strtotime($expiration_date)), $days_remaining, admin_url('admin.php?page=pdf-builder-pro-settings&tab=licence'));
// Set HTML content type
        $headers = ['Content-Type: text/html; charset=UTF-8'];
// Send email
        $sent = wp_mail($notification_email, $subject, $message, $headers);
        if ($sent) {
        // Mark that we sent the notification today
            update_option('pdf_builder_license_last_notification_' . date('Y-m-d'), time());
            error_log('PDF Builder Pro: Expiration notification sent to ' . $notification_email);
        } else {
            error_log('PDF Builder Pro: Failed to send expiration notification to ' . $notification_email);
        }
    }

    /**
     * Clear the scheduled expiration check (call on plugin deactivation)
     */
    public static function clearScheduledExpirationCheck()
    {
        $timestamp = wp_next_scheduled('pdf_builder_checkLicenseExpiration');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'pdf_builder_checkLicenseExpiration');
        }
    }
}
