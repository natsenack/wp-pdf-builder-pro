<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Pro - Deactivation Feedback Modal
 * Recueille le feedback lors de la désactivation du plugin
 */

namespace PDF_Builder\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

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

        // Liens supplémentaires dans la liste des plugins
        add_filter('plugin_action_links_' . plugin_basename(PDF_BUILDER_PLUGIN_FILE), [$this, 'add_plugin_action_links']);

        // Nom du plugin dynamique selon la licence
        add_filter('all_plugins', [$this, 'filter_plugin_name']);
    }

    /**
     * Ajoute des liens Paramètres et Passer en Premium dans la liste des plugins
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=pdf-builder-settings') . '" style="color:#555;">'
            . __('Paramètres', 'pdf-builder-pro') . '</a>';

        array_unshift($links, $settings_link);

        // Afficher le lien Premium uniquement si la licence n'est pas active
        $license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
        if ($license_status !== 'active') {
            $premium_link = '<a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" target="_blank"'
                . ' style="color:#764ba2;font-weight:600;">⭐ ' . __('Passer en Premium', 'pdf-builder-pro') . '</a>';
            array_unshift($links, $premium_link);
        }

        return $links;
    }

    /**
     * Modifie le nom affiché du plugin dans la liste selon le statut de la licence
     */
    public function filter_plugin_name($plugins) {
        $basename = plugin_basename(PDF_BUILDER_PLUGIN_FILE);
        if (!isset($plugins[$basename])) {
            return $plugins;
        }

        $license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
        $plugins[$basename]['Name'] = ($license_status === 'active')
            ? 'PDF Builder Pro'
            : 'PDF Builder';

        return $plugins;
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
        
        // Styles du modal depuis fichier CSS dédié
        wp_enqueue_style(
            'pdf-builder-deactivation-modal',
            plugin_dir_url( PDF_BUILDER_PLUGIN_FILE ) . 'assets/css/deactivation-modal.css',
            [],
            $this->get_version()
        );
    }
    
    /**
     * Traiter le feedback reçu
     */
    public function handle_feedback() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_deactivation_feedback')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        $reason      = isset($_POST['reason'])  ? sanitize_text_field($_POST['reason'])      : 'autre';
        $message     = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $site_url    = get_site_url();
        $admin_email = get_option('admin_email');
        $date_now    = gmdate('d/m/Y H:i:s');

        $subject = "[PDF Builder Pro] Feedback désactivation – {$site_url}";
        $body    = $this->build_feedback_email($reason, $message, $site_url, $admin_email, $date_now);

        // Toujours tenter Gmail SMTP en premier (fiable sur local ET production)
        // wp_mail() est piégé par Local by Flywheel et d'autres outils de dev
        $mail_sent = $this->send_via_phpmailer($subject, $body);

        // Fallback : wp_mail (fonctionne sur hébergements mutualisés en production)
        if (!$mail_sent) {
            error_log('[PDF Builder Pro] Gmail SMTP échoué, tentative wp_mail...');
            $mail_sent = wp_mail($this->email, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
        }

        error_log('[PDF Builder Pro] Feedback – raison: ' . $reason . ' – mail_sent: ' . ($mail_sent ? 'oui' : 'non'));

        wp_send_json_success(['mail_sent' => $mail_sent]);
    }

    /**
     * Déchiffrer le mot de passe SMTP (XOR + base64)
     */
    private function decode_pass($encoded) {
        $key = 'PBP_SECRET_2026!';
        $raw = base64_decode($encoded);
        $out = '';
        for ($i = 0; $i < strlen($raw); $i++) {
            $out .= chr(ord($raw[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return $out;
    }

    /**
     * Envoi via PHPMailer.
     * Priorité : 1) constantes SMTP dans wp-config.php  2) Gmail SMTP fallback
     */
    private function send_via_phpmailer($subject, $body) {
        try {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->SMTPDebug  = 0; // mettre à 2 pour debug verbeux dans error_log
            $mail->Timeout    = 10; // 10 secondes max pour éviter le hang

            // Priorité 1 : constantes définies dans wp-config.php
            if (defined('SMTP_HOST') && SMTP_HOST) {
                $mail->Host       = SMTP_HOST;
                $mail->Username   = defined('SMTP_USER')   ? SMTP_USER   : '';
                $mail->Password   = defined('SMTP_PASS')   ? SMTP_PASS   : '';
                $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = defined('SMTP_PORT')   ? SMTP_PORT   : 587;
                $from             = $mail->Username;
                error_log('[PDF Builder Pro] SMTP via constantes wp-config: ' . $mail->Host);
            } else {
                // Priorité 2 : Gmail SMTP (fallback intégré)
                $mail->Host       = 'smtp.gmail.com';
                $mail->Port       = 465;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
                $mail->Username   = 'threeaxe.france@gmail.com';
                $mail->Password   = $this->decode_pass('JSk3LiEmJCAmPylTXVRQTA==');
                $from             = $mail->Username;
                error_log('[PDF Builder Pro] SMTP via Gmail fallback: ' . $mail->Username);
            }

            $mail->setFrom($from, 'PDF Builder Pro');
            $mail->addAddress($this->email);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $result = $mail->send();
            error_log('[PDF Builder Pro] PHPMailer send() = ' . ($result ? 'OK' : 'FAIL'));
            return $result;
        } catch (\Exception $e) {
            error_log('[PDF Builder Pro] PHPMailer Exception: ' . $e->getMessage());
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

        $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }";
        $html .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
        $html .= '.header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 5px 5px 0 0; }';
        $html .= '.header h2 { margin: 0; font-size: 18px; }';
        $html .= '.content { padding: 20px; border: 1px solid #e0e0e0; border-top: none; }';
        $html .= '.field { margin: 15px 0; }';
        $html .= '.field-label { font-weight: bold; color: #667eea; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }';
        $html .= '.field-value { margin-top: 4px; font-size: 15px; }';
        $html .= '.meta { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 12px; color: #777; }';
        $html .= '</style></head><body><div class="container">';
        $html .= '<div class="header"><h2>Feedback de desactivation - PDF Builder Pro</h2></div>';
        $html .= '<div class="content">';
        $html .= '<div class="field"><div class="field-label">Raison</div><div class="field-value">' . esc_html( $reason_label ) . '</div></div>';

        if (!empty($message)) {
            $html .= '<div class="field"><div class="field-label">Commentaire</div><div class="field-value" style="white-space:pre-wrap;">' . esc_html( $message ) . '</div></div>';
        }

        $html .= '<div class="meta"><strong>Infos du site :</strong><br>';
        $html .= 'URL : ' . esc_html( $site_url ) . '<br>';
        $html .= 'Email admin : ' . esc_html( $admin_email ) . '<br>';
        $html .= 'Serveur : ' . esc_html( $server_soft ) . '<br>';
        $html .= 'Date : ' . esc_html( $date_now ) . '</div>';
        $html .= '</div></div></body></html>';

        return $html;
    }
    
    /**
     * Styles du modal: voir assets/css/deactivation-modal.css
     * @deprecated Remplacé par wp_enqueue_style dans enqueue_deactivation_script()
     */
    private function get_modal_styles() {
        return '';
    }
}

// Initialiser la classe
if (is_admin()) {
    PDF_Builder_Deactivation_Feedback::getInstance();
}
