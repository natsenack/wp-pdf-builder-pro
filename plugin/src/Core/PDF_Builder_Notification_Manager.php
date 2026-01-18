<?php
/**
 * PDF Builder Pro - Notification Manager
 * Système centralisé de gestion des notifications
 * Version: 1.0.0
 * Updated: 2025-11-29
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Classe principale pour la gestion des notifications
 */
class PDF_Builder_Notification_Manager {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * File d'attente des notifications
     */
    private $notification_queue = [];

    /**
     * Paramètres de configuration
     */
    private $settings = [];

    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->init_settings();
        $this->init_hooks();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les paramètres
     */
    private function init_settings() {
        $this->settings = [
            'enabled' => pdf_builder_get_option('pdf_builder_notifications_enabled', true),
            'position' => pdf_builder_get_option('pdf_builder_notifications_position', 'top-right'),
            'duration' => pdf_builder_get_option('pdf_builder_notifications_duration', 5000),
            'max_notifications' => pdf_builder_get_option('pdf_builder_notifications_max', 5),
            'animation' => pdf_builder_get_option('pdf_builder_notifications_animation', 'slide'),
            'sound_enabled' => pdf_builder_get_option('pdf_builder_notifications_sound', false),
            'types' => [
                'success' => ['icon' => '✅', 'color' => '#28a745', 'bg' => '#d4edda'],
                'error' => ['icon' => '❌', 'color' => '#dc3545', 'bg' => '#f8d7da'],
                'warning' => ['icon' => '⚠️', 'color' => '#ffc107', 'bg' => '#fff3cd'],
                'info' => ['icon' => 'ℹ️', 'color' => '#17a2b8', 'bg' => '#d1ecf1']
            ]
        ];
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_pdf_builder_show_notification', [$this, 'ajax_show_notification']);
        add_action('wp_ajax_nopriv_pdf_builder_show_notification', [$this, 'ajax_show_notification']);

        // Hooks pour les notifications automatiques
        add_action('pdf_builder_template_saved', [$this, 'notify_template_saved']);
        add_action('pdf_builder_template_deleted', [$this, 'notify_template_deleted']);
        add_action('pdf_builder_backup_created', [$this, 'notify_backup_created']);
        add_action('pdf_builder_settings_saved', [$this, 'notify_settings_saved']);
    }

    /**
     * Charger les scripts et styles
     */
    public function enqueue_scripts() {
        if (!$this->settings['enabled']) {
            return;
        }

        // Charger les styles et scripts seulement si les fichiers existent
        $notifications_css = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/css/notifications.css';
        if (file_exists($notifications_css)) {
            wp_enqueue_style(
                'pdf-builder-notifications',
                plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/notifications.css',
                [],
                '1.0.0'
            );
        }

        $notifications_js = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/js/notifications.js';
        if (file_exists($notifications_js)) {
            wp_enqueue_script(
                'pdf-builder-notifications',
                plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/notifications.js',
                ['jquery'],
                '1.0.0-' . time(),
                false
            );
            error_log('PDF_Builder_Notification_Manager: script enqueued');
        }

        // Localiser le script avec les paramètres
        wp_localize_script('pdf-builder-notifications', 'pdfBuilderNotifications', [
            'settings' => $this->settings,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_notifications'),
            'strings' => [
                'close' => __('Fermer', 'pdf-builder-pro'),
                'dismiss_all' => __('Tout fermer', 'pdf-builder-pro')
            ]
        ]);
    }

    /**
     * Afficher une notification
     */
    public function show_notification($message, $type = 'info', $options = []) {
        if (!$this->settings['enabled']) {
            return;
        }

        $notification = array_merge([
            'message' => $message,
            'type' => $type,
            'duration' => $this->settings['duration'],
            'dismissible' => true,
            'position' => $this->settings['position'],
            'timestamp' => current_time('timestamp')
        ], $options);

        // Ajouter à la file d'attente
        $this->notification_queue[] = $notification;

        // Limiter le nombre de notifications
        if (count($this->notification_queue) > $this->settings['max_notifications']) {
            array_shift($this->notification_queue);
        }

        // Si on est en AJAX, retourner les données
        if (wp_doing_ajax()) {
            return $notification;
        }

        // Sinon, ajouter au footer
        add_action('wp_footer', [$this, 'render_notifications']);
        add_action('admin_footer', [$this, 'render_notifications']);
    }

    /**
     * Rendre les notifications HTML
     */
    public function render_notifications() {
        if (empty($this->notification_queue)) {
            return;
        }

        echo '<div class="pdf-builder-notifications-container" data-position="' . esc_attr($this->settings['position']) . '">';

        foreach ($this->notification_queue as $notification) {
            $this->render_single_notification($notification);
        }

        echo '</div>';

        // Vider la file d'attente après rendu
        $this->notification_queue = [];
    }

    /**
     * Rendre une notification individuelle
     */
    private function render_single_notification($notification) {
        $type_config = isset($this->settings['types'][$notification['type']])
            ? $this->settings['types'][$notification['type']]
            : $this->settings['types']['info'];

        $classes = [
            'pdf-builder-notification',
            'pdf-builder-notification-' . $notification['type'],
            'pdf-builder-notification-' . $this->settings['animation']
        ];

        if ($notification['dismissible']) {
            $classes[] = 'dismissible';
        }

        $style = sprintf(
            'background-color: %s; color: %s; border-left-color: %s;',
            $type_config['bg'],
            $type_config['color'],
            $type_config['color']
        );

        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>"
             style="<?php echo esc_attr($style); ?>"
             data-duration="<?php echo esc_attr($notification['duration']); ?>">

            <div class="notification-content">
                <span class="notification-icon"><?php echo esc_html($type_config['icon']); ?></span>
                <span class="notification-message"><?php echo wp_kses_post($notification['message']); ?></span>
                <?php if ($notification['dismissible']): ?>
                    <button class="notification-close" aria-label="<?php esc_attr_e('Fermer', 'pdf-builder-pro'); ?>">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($notification['duration'] > 0): ?>
                <div class="notification-progress-bar">
                    <div class="notification-progress" style="background-color: <?php echo esc_attr($type_config['color']); ?>"></div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handler AJAX pour afficher une notification
     */
    public function ajax_show_notification() {
        check_ajax_referer('pdf_builder_notifications', 'nonce');

        $message = sanitize_text_field($_POST['message'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'info');
        $duration = intval($_POST['duration'] ?? $this->settings['duration']);

        if (empty($message)) {
            wp_send_json_error('Message requis');
            return;
        }

        $notification = $this->show_notification($message, $type, ['duration' => $duration]);

        wp_send_json_success([
            'notification' => $notification
        ]);
    }

    /**
     * Notifications automatiques
     */
    public function notify_template_saved($template_id) {
        $message = __('Template sauvegardé avec succès !', 'pdf-builder-pro');
        $this->show_notification($message, 'success');
    }

    public function notify_template_deleted($template_id) {
        $message = __('Template supprimé avec succès.', 'pdf-builder-pro');
        $this->show_notification($message, 'info');
    }

    public function notify_backup_created($backup_path) {
        $message = __('Sauvegarde créée avec succès !', 'pdf-builder-pro');
        $this->show_notification($message, 'success');
    }

    public function notify_settings_saved() {
        $message = __('Paramètres sauvegardés avec succès !', 'pdf-builder-pro');
        $this->show_notification($message, 'success');
    }

    /**
     * Méthodes utilitaires
     */
    public function success($message, $options = []) {
        return $this->show_notification($message, 'success', $options);
    }

    public function error($message, $options = []) {
        return $this->show_notification($message, 'error', $options);
    }

    public function warning($message, $options = []) {
        return $this->show_notification($message, 'warning', $options);
    }

    public function info($message, $options = []) {
        return $this->show_notification($message, 'info', $options);
    }

    /**
     * Obtenir les paramètres actuels
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update_settings($new_settings) {
        $this->settings = array_merge($this->settings, $new_settings);

        // Sauvegarder en base
        foreach ($new_settings as $key => $value) {
            pdf_builder_update_option('pdf_builder_notifications_' . $key, $value);
        }
    }

    /**
     * Vider toutes les notifications
     */
    public function clear_all() {
        $this->notification_queue = [];
    }

    /**
     * Désactiver le système
     */
    public function disable() {
        $this->settings['enabled'] = false;
        pdf_builder_update_option('pdf_builder_notifications_enabled', false);
    }

    /**
     * Activer le système
     */
    public function enable() {
        $this->settings['enabled'] = true;
        pdf_builder_update_option('pdf_builder_notifications_enabled', true);
    }
}

// Fonction globale pour accéder facilement au gestionnaire
function pdf_builder_notifications() {
    return PDF_Builder_Notification_Manager::get_instance();
}

// Fonctions raccourcies
function pdf_builder_notify_success($message, $options = []) {
    return pdf_builder_notifications()->success($message, $options);
}

function pdf_builder_notify_error($message, $options = []) {
    return pdf_builder_notifications()->error($message, $options);
}

function pdf_builder_notify_warning($message, $options = []) {
    return pdf_builder_notifications()->warning($message, $options);
}

function pdf_builder_notify_info($message, $options = []) {
    return pdf_builder_notifications()->info($message, $options);
}


