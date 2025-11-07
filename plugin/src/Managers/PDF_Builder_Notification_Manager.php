<?php
/**
 * PDF Builder Pro - Notification Manager
 *
 * Gère l'envoi des notifications par email pour les événements PDF Builder
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer les notifications par email
 */
class PDF_Builder_Notification_Manager {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Hook pour la génération réussie de PDF
        add_action('pdf_builder_pdf_generated', array($this, 'notify_pdf_generated'), 10, 2);

        // Hook pour les erreurs de génération
        add_action('pdf_builder_pdf_generation_error', array($this, 'notify_pdf_error'), 10, 2);

        // Hook pour la suppression de templates
        add_action('pdf_builder_template_deleted', array($this, 'notify_template_deleted'), 10, 2);

        // Hook pour les avertissements de performance
        add_action('pdf_builder_performance_warning', array($this, 'notify_performance_warning'), 10, 1);
    }

    /**
     * Vérifier si les notifications email sont activées
     */
    private function are_email_notifications_enabled() {
        return get_option('pdf_builder_email_notifications_enabled', false);
    }

    /**
     * Obtenir l'adresse email d'administration
     */
    private function get_admin_email() {
        return get_option('pdf_builder_admin_email', get_option('admin_email'));
    }

    /**
     * Obtenir le niveau de notification
     */
    private function get_notification_level() {
        return get_option('pdf_builder_notification_log_level', 'error');
    }

    /**
     * Vérifier si un niveau de notification doit être envoyé
     */
    private function should_send_notification($level) {
        $current_level = $this->get_notification_level();

        $levels = array(
            'error' => 1,
            'warning' => 2,
            'info' => 3
        );

        return isset($levels[$level]) && isset($levels[$current_level]) &&
               $levels[$level] <= $levels[$current_level];
    }

    /**
     * Envoyer une notification par email
     */
    private function send_notification($subject, $message, $level = 'info') {
        if (!$this->are_email_notifications_enabled()) {
            return false;
        }

        if (!$this->should_send_notification($level)) {
            return false;
        }

        $to = $this->get_admin_email();
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        // Ajouter des informations de debug si activé
        if (get_option('pdf_builder_debug_javascript', false)) {
            $message .= "\n\n--- Debug Info ---\n";
            $message .= "Time: " . current_time('mysql') . "\n";
            $message .= "Site: " . get_bloginfo('url') . "\n";
            $message .= "Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
        }

        $result = wp_mail($to, $subject, nl2br($message), $headers);

        // Logger l'envoi
        if ($result) {
            error_log("PDF Builder: Notification sent - $subject");
        } else {
            error_log("PDF Builder: Failed to send notification - $subject");
        }

        return $result;
    }

    /**
     * Notifier la génération réussie d'un PDF
     */
    public function notify_pdf_generated($order_id, $template_id) {
        if (!get_option('pdf_builder_notification_on_generation', false)) {
            return;
        }

        $subject = sprintf(__('PDF Builder Pro: PDF généré avec succès - Commande #%s', 'pdf-builder-pro'), $order_id);

        $message = sprintf(
            __("Bonjour,\n\nUn PDF a été généré avec succès.\n\nDétails :\n- Commande : #%s\n- Template : %s\n- Date : %s\n\nCordialement,\nL'équipe PDF Builder Pro", 'pdf-builder-pro'),
            $order_id,
            get_the_title($template_id),
            current_time('d/m/Y H:i:s')
        );

        $this->send_notification($subject, $message, 'info');
    }

    /**
     * Notifier une erreur de génération PDF
     */
    public function notify_pdf_error($order_id, $error_message) {
        if (!get_option('pdf_builder_notification_on_error', false)) {
            return;
        }

        $subject = sprintf(__('PDF Builder Pro: Erreur de génération PDF - Commande #%s', 'pdf-builder-pro'), $order_id);

        $message = sprintf(
            __("Bonjour,\n\nUne erreur s'est produite lors de la génération d'un PDF.\n\nDétails :\n- Commande : #%s\n- Erreur : %s\n- Date : %s\n\nVeuillez vérifier les logs pour plus d'informations.\n\nCordialement,\nL'équipe PDF Builder Pro", 'pdf-builder-pro'),
            $order_id,
            $error_message,
            current_time('d/m/Y H:i:s')
        );

        $this->send_notification($subject, $message, 'error');
    }

    /**
     * Notifier la suppression d'un template
     */
    public function notify_template_deleted($template_id, $template_name) {
        if (!get_option('pdf_builder_notification_on_deletion', false)) {
            return;
        }

        $subject = __('PDF Builder Pro: Template supprimé', 'pdf-builder-pro');

        $message = sprintf(
            __("Bonjour,\n\nUn template a été supprimé.\n\nDétails :\n- Template : %s (ID: %s)\n- Supprimé par : %s\n- Date : %s\n\nCordialement,\nL'équipe PDF Builder Pro", 'pdf-builder-pro'),
            $template_name,
            $template_id,
            wp_get_current_user()->display_name,
            current_time('d/m/Y H:i:s')
        );

        $this->send_notification($subject, $message, 'warning');
    }

    /**
     * Notifier un avertissement de performance
     */
    public function notify_performance_warning($warning_message) {
        $subject = __('PDF Builder Pro: Avertissement de performance', 'pdf-builder-pro');

        $message = sprintf(
            __("Bonjour,\n\nUn avertissement de performance a été détecté.\n\nDétails :\n- Message : %s\n- Date : %s\n\nVeuillez vérifier les paramètres de performance.\n\nCordialement,\nL'équipe PDF Builder Pro", 'pdf-builder-pro'),
            $warning_message,
            current_time('d/m/Y H:i:s')
        );

        $this->send_notification($subject, $message, 'warning');
    }

    /**
     * Tester l'envoi de notifications
     */
    public function send_test_notification() {
        $subject = __('PDF Builder Pro: Test de notification', 'pdf-builder-pro');

        $message = sprintf(
            __("Bonjour,\n\nCeci est un email de test du système de notifications PDF Builder Pro.\n\nSi vous recevez cet email, les notifications fonctionnent correctement.\n\nDétails :\n- Date : %s\n- Site : %s\n\nCordialement,\nL'équipe PDF Builder Pro", 'pdf-builder-pro'),
            current_time('d/m/Y H:i:s'),
            get_bloginfo('url')
        );

        return $this->send_notification($subject, $message, 'info');
    }
}