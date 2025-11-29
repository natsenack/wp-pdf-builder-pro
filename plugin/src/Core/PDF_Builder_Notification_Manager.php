<?php
/**
 * PDF Builder Pro - Gestionnaire de Notifications Centralisé
 * Système de notifications simplifié et unifié
 */

class PDF_Builder_Notification_Manager {

    private static $instance = null;
    private $notifications = [];

    /**
     * Singleton pattern
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_pdf_builder_show_notification', [$this, 'ajax_show_notification']);
        add_action('wp_ajax_nopriv_pdf_builder_show_notification', [$this, 'ajax_show_notification']);
    }

    /**
     * Charger les scripts et styles
     */
    public function enqueue_scripts() {
        // Charger seulement sur les pages du plugin
        if (!$this->is_plugin_page()) {
            return;
        }

        wp_enqueue_script(
            'pdf-builder-notifications',
            plugins_url('assets/js/notifications.js', PDF_BUILDER_PLUGIN_FILE),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'pdf-builder-notifications',
            plugins_url('assets/css/notifications.css', PDF_BUILDER_PLUGIN_FILE),
            [],
            '1.0.0'
        );

        // Localiser le script
        wp_localize_script('pdf-builder-notifications', 'pdfBuilderNotifications', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_notifications'),
            'enabled' => $this->is_enabled(),
            'position' => $this->get_position(),
            'duration' => $this->get_duration()
        ]);
    }

    /**
     * Vérifier si on est sur une page du plugin
     */
    private function is_plugin_page() {
        if (!is_admin()) {
            return false;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }

        return strpos($screen->id, 'pdf-builder') !== false ||
               isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false;
    }

    /**
     * Vérifier si les notifications sont activées
     */
    public function is_enabled() {
        return get_option('pdf_builder_notifications_enabled', '1') === '1';
    }

    /**
     * Obtenir la position des notifications
     */
    public function get_position() {
        return get_option('pdf_builder_notifications_position', 'top-right');
    }

    /**
     * Obtenir la durée d'affichage
     */
    public function get_duration() {
        return intval(get_option('pdf_builder_notifications_duration', 5000));
    }

    /**
     * Ajouter une notification
     */
    public function add($message, $type = 'info', $duration = null) {
        if (!$this->is_enabled()) {
            return;
        }

        $notification = [
            'id' => uniqid('notification_'),
            'message' => sanitize_text_field($message),
            'type' => in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'info',
            'duration' => $duration ?: $this->get_duration(),
            'timestamp' => current_time('timestamp')
        ];

        $this->notifications[] = $notification;

        // Si on est en AJAX, retourner la notification
        if (wp_doing_ajax()) {
            return $notification;
        }

        // Sinon, ajouter au footer
        add_action('admin_footer', function() use ($notification) {
            echo $this->render_notification($notification);
        });
    }

    /**
     * Notification de succès
     */
    public function success($message, $duration = null) {
        return $this->add($message, 'success', $duration);
    }

    /**
     * Notification d'erreur
     */
    public function error($message, $duration = null) {
        return $this->add($message, 'error', $duration);
    }

    /**
     * Notification d'avertissement
     */
    public function warning($message, $duration = null) {
        return $this->add($message, 'warning', $duration);
    }

    /**
     * Notification d'information
     */
    public function info($message, $duration = null) {
        return $this->add($message, 'info', $duration);
    }

    /**
     * Rendre une notification HTML
     */
    private function render_notification($notification) {
        $classes = 'pdf-notification pdf-notification-' . $notification['type'];
        $position = $this->get_position();

        return sprintf(
            '<div class="pdf-notification-container %s" data-position="%s">
                <div class="%s" data-id="%s">
                    <div class="pdf-notification-content">
                        <span class="pdf-notification-icon">%s</span>
                        <span class="pdf-notification-message">%s</span>
                        <button class="pdf-notification-close" onclick="PDFBuilderNotifications.close(\'%s\')">×</button>
                    </div>
                </div>
            </div>',
            esc_attr($position),
            esc_attr($position),
            esc_attr($classes),
            esc_attr($notification['id']),
            $this->get_icon($notification['type']),
            esc_html($notification['message']),
            esc_attr($notification['id'])
        );
    }

    /**
     * Obtenir l'icône selon le type
     */
    private function get_icon($type) {
        $icons = [
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️'
        ];

        return isset($icons[$type]) ? $icons[$type] : $icons['info'];
    }

    /**
     * Handler AJAX pour afficher une notification
     */
    public function ajax_show_notification() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_notifications')) {
            wp_send_json_error('Nonce invalide');
            return;
        }

        $message = sanitize_text_field($_POST['message'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'info');
        $duration = intval($_POST['duration'] ?? $this->get_duration());

        if (empty($message)) {
            wp_send_json_error('Message manquant');
            return;
        }

        $notification = $this->add($message, $type, $duration);

        wp_send_json_success([
            'notification' => $notification,
            'html' => $this->render_notification($notification)
        ]);
    }

    /**
     * Obtenir toutes les notifications en attente
     */
    public function get_pending_notifications() {
        return $this->notifications;
    }

    /**
     * Vider les notifications
     */
    public function clear_notifications() {
        $this->notifications = [];
    }
}

// Fonctions helper globales
function pdf_builder_notify($message, $type = 'info', $duration = null) {
    return PDF_Builder_Notification_Manager::get_instance()->add($message, $type, $duration);
}

function pdf_builder_notify_success($message, $duration = null) {
    return PDF_Builder_Notification_Manager::get_instance()->success($message, $duration);
}

function pdf_builder_notify_error($message, $duration = null) {
    return PDF_Builder_Notification_Manager::get_instance()->error($message, $duration);
}

function pdf_builder_notify_warning($message, $duration = null) {
    return PDF_Builder_Notification_Manager::get_instance()->warning($message, $duration);
}

function pdf_builder_notify_info($message, $duration = null) {
    return PDF_Builder_Notification_Manager::get_instance()->info($message, $duration);
}