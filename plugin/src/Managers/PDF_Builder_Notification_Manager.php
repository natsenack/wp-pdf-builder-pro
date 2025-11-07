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

        // Vérifier si SMTP est activé
        $smtp_enabled = get_option('pdf_builder_smtp_enabled', false);
        error_log("PDF Builder: SMTP enabled check - smtp_enabled = " . ($smtp_enabled ? 'true' : 'false'));

        if ($smtp_enabled) {
            error_log("PDF Builder: Using SMTP for notification - $subject");
            $smtp_result = $this->send_smtp_email($to, $subject, $message);
            
            // Si send_smtp_email retourne un array, c'est le nouveau format
            if (is_array($smtp_result)) {
                return $smtp_result['success'];
            }
            return $smtp_result;
        }

        // Utiliser wp_mail() par défaut
        error_log("PDF Builder: Using wp_mail() for notification - $subject");
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
            error_log("PDF Builder: Notification sent via wp_mail() - $subject");
        } else {
            error_log("PDF Builder: Failed to send notification via wp_mail() - $subject");
        }

        return $result;
    }    /**
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
     * Tester l'envoi de notifications - avec retour détaillé
     */
    public function send_test_notification() {
        $subject = __('PDF Builder Pro: Test de notification', 'pdf-builder-pro');

        $message = sprintf(
            __("Bonjour,\n\nCeci est un email de test du système de notifications PDF Builder Pro.\n\nSi vous recevez cet email, les notifications fonctionnent correctement.\n\nDétails :\n- Date : %s\n- Site : %s\n\nCordialement,\nL'équipe PDF Builder Pro", 'pdf-builder-pro'),
            current_time('d/m/Y H:i:s'),
            get_bloginfo('url')
        );

        // Vérifier si SMTP est activé
        $smtp_enabled = get_option('pdf_builder_smtp_enabled', false);
        
        if ($smtp_enabled) {
            // Envoyer via SMTP et capturer l'erreur si elle existe
            $smtp_result = $this->send_smtp_email($this->get_admin_email(), $subject, $message);
            
            // Si send_smtp_email retourne un array avec erreur, la retourner
            if (is_array($smtp_result)) {
                if (!$smtp_result['success'] && $smtp_result['error']) {
                    // Stocker l'erreur pour la retourner au client
                    $GLOBALS['pdf_builder_smtp_error'] = $smtp_result['error'];
                }
                return $smtp_result['success'];
            }
            return $smtp_result;
        }
        
        // Sinon utiliser la méthode standard
        return $this->send_notification($subject, $message, 'info');
    }

    /**
     * Envoyer un email via SMTP - version avec retour d'erreur détaillé
     */
    private function send_smtp_email($to, $subject, $message) {
        error_log("PDF Builder: send_smtp_email called with to=$to, subject=$subject");

        // Récupérer les paramètres SMTP
        $smtp_host = get_option('pdf_builder_smtp_host', '');
        $smtp_port = get_option('pdf_builder_smtp_port', 587);
        $smtp_encryption = get_option('pdf_builder_smtp_encryption', 'tls');
        $smtp_username = get_option('pdf_builder_smtp_username', '');
        $smtp_password = get_option('pdf_builder_smtp_password', '');
        $smtp_from_email = get_option('pdf_builder_smtp_from_email', get_option('admin_email'));
        $smtp_from_name = get_option('pdf_builder_smtp_from_name', get_bloginfo('name'));

        error_log("PDF Builder SMTP Config: Host=$smtp_host, Port=$smtp_port, Encryption=$smtp_encryption, From=$smtp_from_email");

        // Vérifier que les paramètres requis sont présents
        if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
            error_log("PDF Builder: SMTP configuration incomplete - missing required parameters");
            return ['success' => false, 'error' => 'Configuration SMTP incomplète'];
        }

        // Charger PHPMailer si ce n'est pas déjà fait
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        }

        try {
            // Utiliser PHPMailer inclus avec WordPress
            $phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Configuration SMTP
            $phpmailer->isSMTP();
            $phpmailer->Host = $smtp_host;
            $phpmailer->Port = $smtp_port;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_username;
            $phpmailer->Password = $smtp_password;

            // Debug PHPMailer - capturer les messages de débogage
            $debug_output = '';
            $phpmailer->SMTPDebug = 2;
            $phpmailer->Debugoutput = function($str, $level) use (&$debug_output) {
                error_log("PHPMailer Debug [$level]: $str");
                $debug_output .= "[" . $level . "] " . $str . "\n";
            };

            // Configuration du chiffrement
            if ($smtp_encryption === 'ssl') {
                $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtp_encryption === 'tls') {
                $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $phpmailer->SMTPSecure = '';
            }

            // Configuration de l'expéditeur
            $phpmailer->setFrom($smtp_from_email, $smtp_from_name);
            $phpmailer->addAddress($to);

            // Configuration du message
            $phpmailer->isHTML(true);
            $phpmailer->Subject = $subject;
            $phpmailer->Body = nl2br($message);
            $phpmailer->AltBody = strip_tags($message);

            // Log de configuration
            error_log("PDF Builder SMTP Config: Host=$smtp_host, Port=$smtp_port, Encryption=$smtp_encryption, From=$smtp_from_email");

            // Log avant envoi
            error_log("PDF Builder: About to send email via PHPMailer to $to with subject: $subject");

            // Envoyer l'email
            $result = $phpmailer->send();

            // Logger l'envoi
            if ($result) {
                error_log("PDF Builder: Notification sent via SMTP - $subject");
                return ['success' => true, 'error' => null];
            } else {
                error_log("PDF Builder: Failed to send notification via SMTP - " . $phpmailer->ErrorInfo);
                return ['success' => false, 'error' => $phpmailer->ErrorInfo];
            }

        } catch (Exception $e) {
            error_log("PDF Builder: SMTP Exception caught - " . $e->getMessage());
            error_log("PDF Builder: SMTP Exception trace - " . $e->getTraceAsString());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}