<?php
/**
 * PDF Builder Pro - Système de notifications
 * Gère les notifications et alertes pour les administrateurs
 */

class PDF_Builder_Notification_Manager {
    private static $instance = null;

    // Types de notifications
    const TYPE_SUCCESS = 'success';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_CRITICAL = 'critical';

    // Canaux de notification
    const CHANNEL_ADMIN_NOTICE = 'admin_notice';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_LOG = 'log';
    const CHANNEL_DASHBOARD = 'dashboard';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Hooks pour les notifications automatiques
        add_action('pdf_builder_config_updated', [$this, 'notify_config_updated'], 10, 2);
        add_action('pdf_builder_backup_created', [$this, 'notify_backup_created']);
        add_action('pdf_builder_error_occurred', [$this, 'notify_error_occurred']);
        add_action('pdf_builder_security_violation', [$this, 'notify_security_violation']);
        add_action('pdf_builder_update_completed', [$this, 'notify_update_completed']);

        // AJAX pour la gestion des notifications
        add_action('wp_ajax_pdf_builder_dismiss_notification', [$this, 'dismiss_notification_ajax']);
        add_action('wp_ajax_pdf_builder_get_notifications', [$this, 'get_notifications_ajax']);

        // Afficher les notifications dans l'admin
        add_action('admin_notices', [$this, 'display_admin_notices']);

        // Nettoyage périodique
        add_action('pdf_builder_daily_cleanup', [$this, 'cleanup_old_notifications']);
    }

    /**
     * Envoie une notification
     */
    public function send_notification($type, $title, $message, $channels = [], $data = []) {
        $notification = [
            'id' => $this->generate_notification_id(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'timestamp' => current_time('mysql'),
            'read' => false,
            'user_id' => get_current_user_id()
        ];

        // Envoyer via les canaux spécifiés
        if (empty($channels)) {
            $channels = $this->get_default_channels($type);
        }

        foreach ($channels as $channel) {
            $this->send_to_channel($notification, $channel);
        }

        // Stocker la notification
        $this->store_notification($notification);

        return $notification['id'];
    }

    /**
     * Obtient les canaux par défaut pour un type de notification
     */
    private function get_default_channels($type) {
        $channels = [self::CHANNEL_LOG, self::CHANNEL_DASHBOARD];

        switch ($type) {
            case self::TYPE_SUCCESS:
                // Pas de notification admin pour les succès
                break;

            case self::TYPE_INFO:
                $channels[] = self::CHANNEL_ADMIN_NOTICE;
                break;

            case self::TYPE_WARNING:
            case self::TYPE_ERROR:
                $channels[] = self::CHANNEL_ADMIN_NOTICE;
                if (pdf_builder_config('email_notifications_enabled')) {
                    $channels[] = self::CHANNEL_EMAIL;
                }
                break;

            case self::TYPE_CRITICAL:
                $channels[] = self::CHANNEL_ADMIN_NOTICE;
                $channels[] = self::CHANNEL_EMAIL;
                break;
        }

        return $channels;
    }

    /**
     * Envoie une notification via un canal spécifique
     */
    private function send_to_channel($notification, $channel) {
        switch ($channel) {
            case self::CHANNEL_ADMIN_NOTICE:
                $this->add_admin_notice($notification);
                break;

            case self::CHANNEL_EMAIL:
                $this->send_email_notification($notification);
                break;

            case self::CHANNEL_LOG:
                $this->log_notification($notification);
                break;

            case self::CHANNEL_DASHBOARD:
                // Les notifications dashboard sont stockées et affichées dans le widget
                break;
        }
    }

    /**
     * Ajoute une notification admin
     */
    private function add_admin_notice($notification) {
        $transient_key = 'pdf_builder_admin_notices_' . get_current_user_id();
        $notices = get_transient($transient_key);

        if (!$notices) {
            $notices = [];
        }

        $notices[] = $notification;
        set_transient($transient_key, $notices, HOUR_IN_SECONDS);
    }

    /**
     * Envoie une notification par email
     */
    private function send_email_notification($notification) {
        $to = pdf_builder_config('notification_email', get_option('admin_email'));
        $subject = '[PDF Builder Pro] ' . $notification['title'];
        $message = $this->format_email_message($notification);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];

        wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Log une notification
     */
    private function log_notification($notification) {
        if (class_exists('PDF_Builder_Logger')) {
            $logger = PDF_Builder_Logger::get_instance();

            $method_map = [
                self::TYPE_SUCCESS => 'info',
                self::TYPE_INFO => 'info',
                self::TYPE_WARNING => 'warning',
                self::TYPE_ERROR => 'error',
                self::TYPE_CRITICAL => 'critical'
            ];

            $method = $method_map[$notification['type']] ?? 'info';
            $logger->$method($notification['title'] . ': ' . $notification['message'], $notification['data']);
        }
    }

    /**
     * Stocke une notification en base
     */
    private function store_notification($notification) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_notifications';

        // Créer la table si elle n'existe pas
        $this->create_notifications_table();

        $wpdb->insert(
            $table,
            [
                'notification_id' => $notification['id'],
                'type' => $notification['type'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'data' => json_encode($notification['data']),
                'user_id' => $notification['user_id'],
                'read' => $notification['read'] ? 1 : 0,
                'created_at' => $notification['timestamp']
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
        );
    }

    /**
     * Crée la table des notifications
     */
    private function create_notifications_table() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_notifications';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                notification_id varchar(64) NOT NULL,
                type varchar(20) NOT NULL,
                title varchar(255) NOT NULL,
                message text NOT NULL,
                data longtext,
                user_id bigint(20) unsigned,
                read tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY notification_id (notification_id),
                KEY type (type),
                KEY user_id (user_id),
                KEY read (read),
                KEY created_at (created_at)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Génère un ID unique pour une notification
     */
    private function generate_notification_id() {
        return 'pdf_notif_' . wp_generate_password(32, false) . '_' . time();
    }

    /**
     * Formate le message email
     */
    private function format_email_message($notification) {
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo esc_html($notification['title']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007cba; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html($site_name); ?></h1>
                    <h2><?php echo esc_html($notification['title']); ?></h2>
                </div>
                <div class="content">
                    <p><?php echo nl2br(esc_html($notification['message'])); ?></p>

                    <?php if (!empty($notification['data'])): ?>
                        <h3>Détails:</h3>
                        <ul>
                            <?php foreach ($notification['data'] as $key => $value): ?>
                                <li><strong><?php echo esc_html($key); ?>:</strong> <?php echo esc_html(is_array($value) ? json_encode($value) : $value); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=pdf_builder_dashboard')); ?>" class="button">
                            Accéder au tableau de bord
                        </a>
                    </p>
                </div>
                <div class="footer">
                    <p>Cette notification a été envoyée par PDF Builder Pro sur <?php echo esc_html($site_name); ?></p>
                    <p><a href="<?php echo esc_url($site_url); ?>"><?php echo esc_html($site_url); ?></a></p>
                </div>
            </div>
        </body>
        </html>
        <?php

        return ob_get_clean();
    }

    /**
     * Affiche les notifications admin
     */
    public function display_admin_notices() {
        $transient_key = 'pdf_builder_admin_notices_' . get_current_user_id();
        $notices = get_transient($transient_key);

        if (!$notices) {
            return;
        }

        foreach ($notices as $notice) {
            $class = 'notice notice-' . $this->get_notice_class($notice['type']) . ' is-dismissible';
            echo '<div class="' . esc_attr($class) . ' pdf-builder-notice" data-id="' . esc_attr($notice['id']) . '">';
            echo '<p><strong>' . esc_html($notice['title']) . ':</strong> ' . esc_html($notice['message']) . '</p>';
            echo '</div>';
        }

        // Vider les notifications affichées
        delete_transient($transient_key);
    }

    /**
     * Obtient la classe CSS pour le type de notification
     */
    private function get_notice_class($type) {
        $class_map = [
            self::TYPE_SUCCESS => 'success',
            self::TYPE_INFO => 'info',
            self::TYPE_WARNING => 'warning',
            self::TYPE_ERROR => 'error',
            self::TYPE_CRITICAL => 'error'
        ];

        return $class_map[$type] ?? 'info';
    }

    /**
     * Notifications automatiques
     */
    public function notify_config_updated($new_config, $changed_config) {
        $this->send_notification(
            self::TYPE_INFO,
            'Configuration mise à jour',
            'La configuration de PDF Builder Pro a été modifiée.',
            [],
            ['changed_keys' => array_keys($changed_config)]
        );
    }

    public function notify_backup_created($backup_data) {
        if (pdf_builder_config('notify_on_backups')) {
            $this->send_notification(
                self::TYPE_SUCCESS,
                'Sauvegarde créée',
                'Une nouvelle sauvegarde a été créée avec succès.',
                [],
                $backup_data
            );
        }
    }

    public function notify_error_occurred($error_data) {
        if (pdf_builder_config('notify_on_errors')) {
            $this->send_notification(
                self::TYPE_ERROR,
                'Erreur détectée',
                'Une erreur s\'est produite dans PDF Builder Pro.',
                [],
                $error_data
            );
        }
    }

    public function notify_security_violation($violation_data) {
        $this->send_notification(
            self::TYPE_CRITICAL,
            'Violation de sécurité',
            'Une tentative de violation de sécurité a été détectée.',
            [self::CHANNEL_EMAIL, self::CHANNEL_LOG],
            $violation_data
        );
    }

    public function notify_update_completed($update_data) {
        $this->send_notification(
            self::TYPE_SUCCESS,
            'Mise à jour terminée',
            'PDF Builder Pro a été mis à jour avec succès.',
            [],
            $update_data
        );
    }

    /**
     * AJAX - Marque une notification comme lue
     */
    public function dismiss_notification_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $notification_id = sanitize_text_field($_POST['notification_id'] ?? '');

            if (empty($notification_id)) {
                wp_send_json_error(['message' => 'ID de notification manquant']);
                return;
            }

            global $wpdb;
            $table = $wpdb->prefix . 'pdf_builder_notifications';

            $wpdb->update(
                $table,
                ['read' => 1],
                ['notification_id' => $notification_id],
                ['%d'],
                ['%s']
            );

            wp_send_json_success(['message' => 'Notification marquée comme lue']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Récupère les notifications
     */
    public function get_notifications_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $limit = intval($_POST['limit'] ?? 50);
            $offset = intval($_POST['offset'] ?? 0);
            $type = sanitize_text_field($_POST['type'] ?? '');
            $read = $_POST['read'] ?? null;

            $notifications = $this->get_notifications($limit, $offset, $type, $read);

            wp_send_json_success(['notifications' => $notifications]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Récupère les notifications
     */
    public function get_notifications($limit = 50, $offset = 0, $type = '', $read = null) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_notifications';

        $where = [];
        $where_values = [];

        if (!empty($type)) {
            $where[] = 'type = %s';
            $where_values[] = $type;
        }

        if ($read !== null) {
            $where[] = 'read = %d';
            $where_values[] = $read ? 1 : 0;
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = $wpdb->prepare("
            SELECT * FROM $table
            $where_clause
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", array_merge($where_values, [$limit, $offset]));

        $results = $wpdb->get_results($query, ARRAY_A);

        // Décoder les données JSON
        foreach ($results as &$result) {
            $result['data'] = json_decode($result['data'], true);
            $result['read'] = (bool) $result['read'];
        }

        return $results;
    }

    /**
     * Nettoie les anciennes notifications
     */
    public function cleanup_old_notifications() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_notifications';

        // Supprimer les notifications lues de plus de 30 jours
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));

        // Supprimer les notifications non lues de plus de 90 jours
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE read = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        ));
    }

    /**
     * Obtient le nombre de notifications non lues
     */
    public function get_unread_count() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_notifications';

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE read = 0");
    }
}

// Fonctions globales
function pdf_builder_notify($type, $title, $message, $channels = [], $data = []) {
    return PDF_Builder_Notification_Manager::get_instance()->send_notification($type, $title, $message, $channels, $data);
}

function pdf_builder_get_notifications($limit = 50, $offset = 0, $type = '', $read = null) {
    return PDF_Builder_Notification_Manager::get_instance()->get_notifications($limit, $offset, $type, $read);
}

function pdf_builder_get_unread_notifications_count() {
    return PDF_Builder_Notification_Manager::get_instance()->get_unread_count();
}

// Initialiser le gestionnaire de notifications
add_action('plugins_loaded', function() {
    PDF_Builder_Notification_Manager::get_instance();
});