<?php
/**
 * PDF Builder Pro - Deactivation Feedback Modal
 * Recueille le feedback lors de la désactivation du plugin
 */

namespace PDF_Builder\Admin;

if (!defined('ABSPATH')) exit;

class PDF_Builder_Deactivation_Feedback {
    
    private static $instance = null;
    private $email = 'threeaxe.france@gmail.com';
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Hook pour enqueuer le script sur la page des plugins
        add_action('admin_enqueue_scripts', [$this, 'enqueue_deactivation_script']);
        
        // AJAX endpoint pour recevoir le feedback
        add_action('wp_ajax_pdf_builder_send_deactivation_feedback', [$this, 'handle_feedback']);
        add_action('wp_ajax_nopriv_pdf_builder_send_deactivation_feedback', [$this, 'handle_feedback']);
    }
    
    /**
     * Récupérer la version du plugin
     */
    private function get_version() {
        // Lire depuis le header du fichier principal du plugin
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $plugin_data = get_plugin_data(PDF_BUILDER_PLUGIN_FILE);
        return isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
    }
    
    /**
     * Enqueuer le script de feedback sur la page des plugins
     */
    public function enqueue_deactivation_script($hook) {
        // Seulement sur la page de gestion des plugins
        if ($hook !== 'plugins.php') {
            return;
        }
        
        wp_enqueue_script(
            'pdf-builder-deactivation-feedback',
            plugin_dir_url(PDF_BUILDER_PLUGIN_FILE) . 'assets/js/deactivation-feedback.min.js',
            ['jquery'],
            $this->get_version(),
            true
        );
        
        wp_localize_script('pdf-builder-deactivation-feedback', 'pdfBuilderDeactivation', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_deactivation_feedback'),
            'pluginSlug' => 'pdf-builder-pro',
        ]);
        
        // Inline styles pour le modal
        wp_add_inline_style('wp-admin', $this->get_modal_styles());
    }
    
    /**
     * Traiter le feedback reçu
     */
    public function handle_feedback() {
        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_deactivation_feedback')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Récupérer les données
        $reason  = isset($_POST['reason'])  ? sanitize_text_field($_POST['reason'])      : 'autre';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $site_url    = get_site_url();
        $admin_email = get_option('admin_email');
        $date_now    = date('d/m/Y H:i:s');

        // 1. SAUVEGARDE EN BASE — toujours fiable
        $feedbacks   = get_option('pbp_deactivation_feedbacks', []);
        $feedbacks[] = [
            'date'    => $date_now,
            'reason'  => $reason,
            'message' => $message,
            'site'    => $site_url,
            'admin'   => $admin_email,
        ];
        update_option('pbp_deactivation_feedbacks', $feedbacks, false);

        // 2. ENVOI EMAIL via wp_mail + capture d'erreur
        $mail_error  = null;
        $mail_sent   = false;

        // Capturer l'erreur PHPMailer si wp_mail échoue
        add_action('wp_mail_failed', function($wp_error) use (&$mail_error) {
            $mail_error = $wp_error->get_error_message();
            error_log('[PDF Builder Pro] wp_mail failed: ' . $mail_error);
        });

        $email_subject = "[PDF Builder Pro] Feedback désactivation – {$site_url}";
        $email_body    = $this->build_feedback_email($reason, $message, $site_url, $admin_email, $date_now);

        $mail_sent = wp_mail(
            $this->email,
            $email_subject,
            $email_body,
            ['Content-Type: text/html; charset=UTF-8']
        );

        // Si wp_mail échoue, essayer via PHPMailer SMTP direct (si défini en constantes WP)
        if (!$mail_sent && defined('SMTP_HOST') && SMTP_HOST) {
            $mail_sent = $this->send_via_smtp($email_subject, $email_body);
        }

        // Log du résultat
        if (!$mail_sent) {
            error_log('[PDF Builder Pro] Mail non envoyé. Erreur: ' . ($mail_error ?: 'inconnue') . ' – Feedback sauvegardé en DB.');
        }

        // Retourner succès (le feedback est sauvegardé en DB quoi qu'il arrive)
        wp_send_json_success(['saved' => true, 'mail_sent' => $mail_sent]);
    }

    /**
     * Envoi SMTP direct via PHPMailer (fallback si wp_mail ne fonctionne pas)
     * Utilise les constantes définies dans wp-config.php : SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS
     */
    private function send_via_smtp($subject, $body) {
        try {
            global $phpmailer;
            if (!is_object($phpmailer) || !($phpmailer instanceof PHPMailer\PHPMailer\PHPMailer)) {
                require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                $phpmailer = new PHPMailer\PHPMailer\PHPMailer(true);
            }

            $phpmailer->isSMTP();
            $phpmailer->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Username   = defined('SMTP_USER') ? SMTP_USER : '';
            $phpmailer->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
            $phpmailer->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
            $phpmailer->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
            $phpmailer->setFrom(defined('SMTP_USER') ? SMTP_USER : get_option('admin_email'), 'PDF Builder Pro');
            $phpmailer->addAddress($this->email);
            $phpmailer->isHTML(true);
            $phpmailer->Subject = $subject;
            $phpmailer->Body    = $body;
            return $phpmailer->send();
        } catch (\Exception $e) {
            error_log('[PDF Builder Pro] SMTP direct failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Construire le contenu de l'email de feedback
     */
    private function build_feedback_email($reason, $message, $site_url, $admin_email, $date_now) {
        $reason_labels = [
            'dont_need'   => "N'en a plus besoin",
            'not_working' => 'Le plugin ne fonctionne pas correctement',
            'slow'        => 'Le plugin ralentit le site',
            'confusing'   => 'Le plugin est difficile à utiliser',
            'expensive'   => 'Trop cher pour les fonctionnalités',
            'alternative' => 'Meilleure alternative trouvée',
            'temporary'   => 'Désactivation temporaire',
            'autre'       => 'Autre raison',
        ];

        $reason_label  = isset($reason_labels[$reason]) ? $reason_labels[$reason] : $reason;
        $server_soft   = isset($_SERVER['SERVER_SOFTWARE']) ? esc_html($_SERVER['SERVER_SOFTWARE']) : 'N/A';

        $html = <<<EMAIL
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .header h2 { margin: 0; font-size: 18px; }
        .content { padding: 20px; border: 1px solid #e0e0e0; border-top: none; }
        .field { margin: 15px 0; }
        .field-label { font-weight: bold; color: #667eea; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
        .field-value { margin-top: 4px; font-size: 15px; }
        .meta { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔔 Feedback de désactivation – PDF Builder Pro</h2>
        </div>
        <div class="content">
            <div class="field">
                <div class="field-label">Raison</div>
                <div class="field-value">{$reason_label}</div>
            </div>
EMAIL;

        if (!empty($message)) {
            $html .= <<<EMAIL
            <div class="field">
                <div class="field-label">Commentaire</div>
                <div class="field-value" style="white-space:pre-wrap;">{$message}</div>
            </div>
EMAIL;
        }

        $html .= <<<EMAIL
            <div class="meta">
                <strong>Infos du site :</strong><br>
                URL : {$site_url}<br>
                Email admin : {$admin_email}<br>
                Serveur : {$server_soft}<br>
                Date : {$date_now}
            </div>
        </div>
    </div>
</body>
</html>
EMAIL;

        return $html;
    }
    
    /**
     * Retourner les styles CSS du modal
     */
    private function get_modal_styles() {
        return <<<CSS
#pdf-builder-deactivation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999999;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
}

#pdf-builder-deactivation-modal.show {
    display: flex;
}

#pdf-builder-deactivation-modal .modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 90%;
    padding: 0;
    overflow: hidden;
}

#pdf-builder-deactivation-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    margin: 0;
}

#pdf-builder-deactivation-modal .modal-header h2 {
    margin: 0;
    font-size: 18px;
}

#pdf-builder-deactivation-modal .modal-body {
    padding: 20px;
}

#pdf-builder-deactivation-modal .feedback-group {
    margin-bottom: 15px;
}

#pdf-builder-deactivation-modal .feedback-option {
    display: flex;
    align-items: flex-start;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    cursor: pointer;
    margin-bottom: 8px;
    transition: all 0.2s;
}

#pdf-builder-deactivation-modal .feedback-option:hover {
    background: #f9f9f9;
    border-color: #667eea;
}

#pdf-builder-deactivation-modal .feedback-option input[type="radio"] {
    margin-top: 2px;
    margin-right: 10px;
    cursor: pointer;
}

#pdf-builder-deactivation-modal .feedback-option label {
    cursor: pointer;
    flex: 1;
}

#pdf-builder-deactivation-modal textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    font-family: inherit;
    display: none;
}

#pdf-builder-deactivation-modal textarea.show {
    display: block;
}

#pdf-builder-deactivation-modal .email-field {
    margin-bottom: 15px;
    display: none;
}

#pdf-builder-deactivation-modal .email-field.show {
    display: block;
}

#pdf-builder-deactivation-modal .email-field input {
    width: 100%;
    padding: 8px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    box-sizing: border-box;
}

#pdf-builder-deactivation-modal .modal-footer {
    display: flex;
    gap: 10px;
    padding: 20px;
    background: #f5f5f5;
    justify-content: space-between;
}

#pdf-builder-deactivation-modal .skip-button {
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: #999;
    cursor: pointer;
    font-size: 12px;
    opacity: 0.6;
    transition: opacity 0.2s;
}

#pdf-builder-deactivation-modal .skip-button:hover {
    opacity: 0.8;
}

#pdf-builder-deactivation-modal .deactivate-button {
    padding: 10px 20px;
    border: none;
    background: #dc3545;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}

#pdf-builder-deactivation-modal .deactivate-button:hover {
    background: #c82333;
}

#pdf-builder-deactivation-modal .deactivate-button.loading {
    opacity: 0.6;
    cursor: not-allowed;
}

#pdf-builder-deactivation-modal .deactivate-button.loading:after {
    content: '...';
}
CSS;
    }
}

// Initialiser la classe
if (is_admin()) {
    PDF_Builder_Deactivation_Feedback::getInstance();
}
