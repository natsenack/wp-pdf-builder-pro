<?php
/**
 * Gestionnaire unifié des notifications pour PDF Builder Pro
 *
 * @package PDF_Builder_Pro
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer les notifications de manière unifiée
 */
class PDF_Builder_Notification_Manager {

    /**
     * Types de notification supportés
     */
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    /**
     * Styles de notification supportés
     */
    const STYLE_TOAST = 'toast';
    const STYLE_INLINE = 'inline';
    const STYLE_ADMIN_NOTICE = 'admin_notice';

    /**
     * Instance unique (Singleton)
     */
    private static $instance = null;

    /**
     * File d'attente des notifications
     */
    private $notification_queue = [];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_pdf_builder_dismiss_notification', [$this, 'ajax_dismiss_notification']);
        add_action('admin_notices', [$this, 'render_admin_notices']);
        add_action('wp_footer', [$this, 'render_toast_container']);
        add_action('admin_footer', [$this, 'render_toast_container']);
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
     * Enqueue les scripts et styles nécessaires
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'pdf-builder-notifications',
            plugins_url('assets/js/notifications.js', dirname(__FILE__)),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'pdf-builder-notifications',
            plugins_url('assets/css/notifications.css', dirname(__FILE__)),
            [],
            '1.0.0'
        );

        wp_localize_script('pdf-builder-notifications', 'pdfBuilderNotifications', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_notifications'),
            'strings' => [
                'dismiss' => __('Fermer', 'pdf-builder-pro'),
            ]
        ]);
    }

    /**
     * Ajouter une notification toast (petite notification en haut à droite)
     */
    public function add_toast($message, $type = self::TYPE_SUCCESS, $duration = 4000) {
        $this->notification_queue[] = [
            'id' => uniqid('toast_'),
            'message' => $message,
            'type' => $type,
            'style' => self::STYLE_TOAST,
            'duration' => $duration,
            'dismissible' => true
        ];
    }

    /**
     * Ajouter une notification inline (dans le contenu de la page)
     */
    public function add_inline($message, $type = self::TYPE_SUCCESS, $dismissible = true) {
        $this->notification_queue[] = [
            'id' => uniqid('inline_'),
            'message' => $message,
            'type' => $type,
            'style' => self::STYLE_INLINE,
            'dismissible' => $dismissible
        ];
    }

    /**
     * Ajouter une notification admin (bannière WordPress standard)
     */
    public function add_admin_notice($message, $type = self::TYPE_SUCCESS, $dismissible = true) {
        $this->notification_queue[] = [
            'id' => uniqid('admin_'),
            'message' => $message,
            'type' => $type,
            'style' => self::STYLE_ADMIN_NOTICE,
            'dismissible' => $dismissible
        ];
    }

    /**
     * Rendre le conteneur pour les toasts
     */
    public function render_toast_container() {
        if (!is_admin()) {
            return;
        }
        echo '<div id="pdf-builder-toast-container" style="position: fixed; top: 40px; right: 20px; z-index: 10000; pointer-events: none;"></div>';
    }

    /**
     * Rendre les notifications admin
     */
    public function render_admin_notices() {
        foreach ($this->notification_queue as $notification) {
            if ($notification['style'] === self::STYLE_ADMIN_NOTICE) {
                $this->render_admin_notice($notification);
            }
        }
    }

    /**
     * Rendre une notification admin
     */
    private function render_admin_notice($notification) {
        $class = 'notice notice-' . $notification['type'];
        if ($notification['dismissible']) {
            $class .= ' is-dismissible';
        }

        echo '<div class="' . esc_attr($class) . '">';
        echo '<p>' . wp_kses($notification['message'], $this->get_allowed_html()) . '</p>';
        echo '</div>';
    }

    /**
     * Obtenir les données JSON des notifications pour JavaScript
     */
    public function get_notifications_json() {
        $toasts = array_filter($this->notification_queue, function($n) {
            return $n['style'] === self::STYLE_TOAST;
        });

        return wp_json_encode($toasts);
    }

    /**
     * AJAX handler pour marquer une notification comme lue
     */
    public function ajax_dismiss_notification() {
        check_ajax_referer('pdf_builder_notifications', 'nonce');

        $notification_id = sanitize_text_field($_POST['notification_id'] ?? '');

        if ($notification_id) {
            // Ici on pourrait stocker les notifications lues en base de données
            // Pour l'instant, on ne fait que confirmer
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Rendre toutes les notifications en attente
     */
    public function render_all() {
        // Injecter les données des toasts dans la page
        add_action('admin_footer', function() {
            $toast_data = $this->get_notifications_json();
            echo "<script>window.pdfBuilderToasts = {$toast_data};</script>";
        });
    }

    /**
     * HTML autorisé pour les messages
     */
    private function get_allowed_html() {
        return [
            'strong' => [],
            'em' => [],
            'br' => [],
            'a' => [
                'href' => [],
                'target' => [],
                'rel' => []
            ],
            'span' => [
                'class' => []
            ]
        ];
    }

    /**
     * Méthodes statiques pour un accès facile
     */
    public static function success($message, $style = self::STYLE_TOAST) {
        $instance = self::get_instance();
        if ($style === self::STYLE_TOAST) {
            $instance->add_toast($message, self::TYPE_SUCCESS);
        } elseif ($style === self::STYLE_ADMIN_NOTICE) {
            $instance->add_admin_notice($message, self::TYPE_SUCCESS);
        } else {
            $instance->add_inline($message, self::TYPE_SUCCESS);
        }
    }

    public static function error($message, $style = self::STYLE_TOAST) {
        $instance = self::get_instance();
        if ($style === self::STYLE_TOAST) {
            $instance->add_toast($message, self::TYPE_ERROR);
        } elseif ($style === self::STYLE_ADMIN_NOTICE) {
            $instance->add_admin_notice($message, self::TYPE_ERROR);
        } else {
            $instance->add_inline($message, self::TYPE_ERROR);
        }
    }

    public static function warning($message, $style = self::STYLE_TOAST) {
        $instance = self::get_instance();
        if ($style === self::STYLE_TOAST) {
            $instance->add_toast($message, self::TYPE_WARNING);
        } elseif ($style === self::STYLE_ADMIN_NOTICE) {
            $instance->add_admin_notice($message, self::TYPE_WARNING);
        } else {
            $instance->add_inline($message, self::TYPE_WARNING);
        }
    }

    public static function info($message, $style = self::STYLE_TOAST) {
        $instance = self::get_instance();
        if ($style === self::STYLE_TOAST) {
            $instance->add_toast($message, self::TYPE_INFO);
        } elseif ($style === self::STYLE_ADMIN_NOTICE) {
            $instance->add_admin_notice($message, self::TYPE_INFO);
        } else {
            $instance->add_inline($message, self::TYPE_INFO);
        }
    }
}